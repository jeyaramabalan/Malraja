<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PurchaseOrderModel;
use App\Models\PurchaseOrderDetailModel;
use App\Models\OrderModel;
use App\Models\OrderDetailModel;
use App\Models\StockModel;
use App\Models\ProductsModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\PurchaseDeletionExport;
use Illuminate\Support\Facades\Log;

class PurchaseDeletionController extends Controller
{
    public function index()
    {
        // === START OF THE FIX ===
        // The query now joins with the 'vendor' table to get the name.
        $purchaseOrders = PurchaseOrderModel::select(
                'purchase_order.id', 
                'purchase_order.bill_no', 
                'purchase_order.date',
                'vendor.name as vendor_name' // Get the vendor name
            )
            ->leftJoin('vendor', 'purchase_order.customer_id', '=', 'vendor.id')
            ->orderBy('purchase_order.date', 'desc')
            ->get();
        // === END OF THE FIX ===

        return view('purchase_orders.delete_page', ['purchaseOrders' => $purchaseOrders]);
    }

    private function getTrueStock($productId) {
        $stock = DB::table('stock')
            ->where('product_id', $productId)
            ->select(
                DB::raw('SUM(purchase) as total_purchase'),
                DB::raw('SUM(sale) as total_sale')
            )->first();
        return ($stock->total_purchase ?? 0) - ($stock->total_sale ?? 0);
    }

    public function destroy(Request $request)
    {
        $request->validate(['purchase_order_id' => 'required|exists:purchase_order,id']);
        $purchaseOrderId = $request->input('purchase_order_id');

        $purchaseOrderForInfo = PurchaseOrderModel::find($purchaseOrderId);
        if (!$purchaseOrderForInfo) {
            return response()->json(['message' => 'Deletion failed: Purchase Order could not be found.'], 404);
        }
        $billNoForFile = $purchaseOrderForInfo->bill_no;

        $deletedItemsLog = [];
        $sqlRollbackScript = "-- SQL Rollback Script for Purchase Order Deletion --\n";
        $sqlRollbackScript .= "-- Generated on: " . now() . "\n\n";
        
        try {
            $purchaseOrderDetailsForLog = PurchaseOrderDetailModel::where('order_id', $purchaseOrderId)->get();
            $productIds = $purchaseOrderDetailsForLog->pluck('product_id')->unique();
            $stockBeforeMap = [];
            foreach ($productIds as $id) {
                $stockBeforeMap[$id] = $this->getTrueStock($id);
            }

            DB::transaction(function () use ($purchaseOrderId, &$deletedItemsLog, &$sqlRollbackScript, $stockBeforeMap) {
                
                $purchaseOrder = PurchaseOrderModel::findOrFail($purchaseOrderId);
                $purchaseOrderDetails = PurchaseOrderDetailModel::where('order_id', $purchaseOrderId)->get();
                
                $sqlRollbackScript .= "-- Step 1: Re-insert Purchase Data --\n";
                $poData = $purchaseOrder->getAttributes();
                $poColumns = implode('`, `', array_keys($poData));
                $poValues = implode("', '", array_map('addslashes', array_values($poData)));
                $sqlRollbackScript .= "INSERT INTO `purchase_order` (`{$poColumns}`) VALUES ('{$poValues}');\n";
                
                $sqlRollbackScript .= "\n-- Step 2: Re-insert/Update Deleted Sales Data --\n";
                foreach ($purchaseOrderDetails as $poDetail) {
                    $podData = $poDetail->getAttributes();
                    $podColumns = implode('`, `', array_keys($podData));
                    $podValues = implode("', '", array_map('addslashes', array_values($podData)));
                    $sqlRollbackScript .= "INSERT INTO `purchase_order_details` (`{$podColumns}`) VALUES ('{$podValues}');\n";

                    $product = ProductsModel::find($poDetail->product_id);
                    $productName = $product ? $product->name : 'Unknown Product (ID: ' . $poDetail->product_id . ')';
                    
                    $deletedItemsLog[] = [
                        'Record Type' => 'Purchase Item', 'Bill Number' => $purchaseOrder->bill_no,
                        'Product Name' => $productName, 'Quantity Change' => -$poDetail->quantity,
                        'Reason' => 'Original Purchase Deleted', 'Product Stock Before' => $stockBeforeMap[$poDetail->product_id] ?? 'N/A',
                        'Product Stock After' => 'N/A', 'Bill Total Before' => $purchaseOrder->total, 'Bill Total After' => 0
                    ];
                    
                    $quantityToClear = $poDetail->quantity;

                    $salesDetails = OrderDetailModel::where('order_details.product_id', $poDetail->product_id)
                        ->join('order', 'order_details.order_id', '=', 'order.id')
                        ->orderBy('order.date', 'desc')->orderBy('order.id', 'desc')
                        ->select('order_details.*')->get();

                    foreach ($salesDetails as $saleDetail) {
                        if ($quantityToClear <= 0) break;
                        $saleOrder = OrderModel::find($saleDetail->order_id);
                        if (!$saleOrder) continue;
                        $quantityToRemove = min($quantityToClear, $saleDetail->quantity);
                        $billTotalBefore = $saleOrder->total;
                        
                        $sdData = $saleDetail->getAttributes();
                        $sdColumns = implode('`, `', array_keys($sdData));
                        $sdValues = implode("', '", array_map('addslashes', array_values($sdData)));
                        $sqlRollbackScript .= "INSERT INTO `order_details` (`{$sdColumns}`) VALUES ('{$sdValues}') ON DUPLICATE KEY UPDATE `quantity`=VALUES(`quantity`), `amount`=VALUES(`amount`);\n";
                        
                        $saleStockEntry = StockModel::where('bill', $saleOrder->bill_id)->where('product_id', $saleDetail->product_id)->where('type', 'sale')->first();
                        if ($saleStockEntry) {
                             $sqlRollbackScript .= "UPDATE `stock` SET `sale` = '{$saleStockEntry->sale}' WHERE `id` = '{$saleStockEntry->id}';\n";
                             $saleStockEntry->sale -= $quantityToRemove;
                             if ($saleStockEntry->sale > 0.001) { $saleStockEntry->save(); } else { $saleStockEntry->forceDelete(); }
                        }
                        
                        if ($quantityToRemove < $saleDetail->quantity) {
                            $saleDetail->quantity -= $quantityToRemove;
                            $saleDetail->save();
                        } else {
                            $saleDetail->forceDelete();
                        }

                        $remainingItemsTotal = OrderDetailModel::where('order_id', $saleOrder->id)->sum('amount');
                        $newOrderTotal = ($remainingItemsTotal > 0) ? $remainingItemsTotal : 0;
                        if ($remainingItemsTotal > 0) {
                            $sqlRollbackScript .= "UPDATE `order` SET `total` = '{$saleOrder->total}' WHERE `id` = '{$saleOrder->id}';\n";
                            $saleOrder->total = $newOrderTotal;
                            $saleOrder->save();
                        } else {
                            $soData = $saleOrder->getAttributes();
                            $soColumns = implode('`, `', array_keys($soData));
                            $soValues = implode("', '", array_map('addslashes', array_values($soData)));
                            $sqlRollbackScript .= "INSERT INTO `order` (`{$soColumns}`) VALUES ('{$soValues}');\n";
                            $saleOrder->forceDelete();
                        }
                        
                        $deletedItemsLog[] = [
                            'Record Type' => 'Sales Item', 'Bill Number' => $saleOrder->bill_id,
                            'Product Name' => $productName, 'Quantity Change' => "+{$quantityToRemove}",
                            'Reason' => 'Auto-reversed by Purchase Deletion', 'Product Stock Before' => 'N/A',
                            'Product Stock After' => 'N/A', 'Bill Total Before' => $billTotalBefore, 'Bill Total After' => $newOrderTotal
                        ];
                        $quantityToClear -= $quantityToRemove;
                    }
                }

                StockModel::where('bill', $purchaseOrder->bill_no)->where('type', 'purchase')->forceDelete();
                PurchaseOrderDetailModel::where('order_id', $purchaseOrder->id)->forceDelete();
                $purchaseOrder->forceDelete();
            });

            $stockAfterMap = [];
            foreach ($productIds as $id) {
                $stockAfterMap[$id] = $this->getTrueStock($id);
            }

            foreach ($deletedItemsLog as $key => $log) {
                if ($log['Record Type'] === 'Purchase Item') {
                    $product = ProductsModel::where('name', $log['Product Name'])->first();
                    if ($product) {
                        $deletedItemsLog[$key]['Product Stock After'] = $stockAfterMap[$product->id] ?? 'N/A';
                    }
                }
            }

        } 
        catch (\Exception $e) {
            Log::error('Purchase Deletion Failed: ' . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine());
            return response()->json(['message' => 'An unexpected server error occurred: ' . $e->getMessage()], 500);
        }

        $timestamp = now()->format('Y-m-d_His');
        $excelFileName = "deletion_log_{$billNoForFile}_{$timestamp}.xlsx";
        $sqlFileName = "rollback_script_{$billNoForFile}_{$timestamp}.sql";

        Storage::disk('local')->put($sqlFileName, $sqlRollbackScript);
        Excel::store(new PurchaseDeletionExport($deletedItemsLog), $excelFileName, 'local');
        
        return response()->json([
            'message' => 'Deletion successful. Backup files are ready.',
            'excel_url' => route('purchase.download.file', ['filename' => $excelFileName]),
            'sql_url' => route('purchase.download.file', ['filename' => $sqlFileName]),
        ]);
    }

    public function downloadFile(Request $request)
    {
        $filename = $request->query('filename');
        $filename = basename($filename);
        $path = storage_path('app/' . $filename);

        if (Storage::disk('local')->exists($filename)) {
            return response()->download($path)->deleteFileAfterSend(true);
        }
        abort(404, 'File not found or has already been downloaded.');
    }
}