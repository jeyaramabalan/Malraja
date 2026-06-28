<?php

namespace App\Http\Controllers\API;

use App\Models\CustomerModel;
use App\Models\OrderDetailModel;
use App\Models\OrderModel;
use App\Models\ProductsModel;
use App\Models\StockModel;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class DesktopPosController extends BaseController
{
    public function getProductCodes(Request $request)
    {
        try {
            $rows = ProductsModel::where('status', 1)
                ->whereNotNull('code')
                ->where('code', '!=', '')
                ->orderBy('code')
                ->pluck('code')
                ->toArray();
            return $this->sendResponse(['codes' => $rows], 'Product codes fetched successfully.', 1);
        } catch (Exception $e) {
            return $this->sendError($e->getMessage(), [], 200);
        }
    }

    public function getCustomerNames(Request $request)
    {
        try {
            $rows = CustomerModel::where('status', 1)
                ->whereNotNull('name')
                ->where('name', '!=', '')
                ->orderBy('name')
                ->pluck('name')
                ->toArray();
            return $this->sendResponse(['names' => $rows], 'Customer names fetched successfully.', 1);
        } catch (Exception $e) {
            return $this->sendError($e->getMessage(), [], 200);
        }
    }

    public function getProductByCode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_code' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors()->first(), [], 200);
        }

        try {
            $product = ProductsModel::select(
                'id',
                'name',
                'tamil_name',
                'code',
                'hsn as hsn_id',
                'category_id',
                'unit',
                'customer_rate as rate',
                'final_price as ws_rate',
                'purchase_rate',
                'gst',
                'additional_tax',
                'status'
            )
                ->where('code', $request->input('product_code'))
                ->where('status', 1)
                ->first();

            if (empty($product)) {
                return $this->sendError('Product not found.', [], 200);
            }

            return $this->sendResponse($product->toArray(), 'Product fetched successfully.');
        } catch (Exception $e) {
            return $this->sendError($e->getMessage(), [], 200);
        }
    }

    public function getCustomerByName(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors()->first(), [], 200);
        }

        try {
            $customer = CustomerModel::select('id', 'name', 'mobile', 'address')
                ->where('name', $request->input('name'))
                ->where('status', 1)
                ->first();

            if (empty($customer)) {
                return $this->sendResponse([
                    'id' => 1,
                    'name' => 'Cash Bill',
                    'mobile' => '',
                    'address' => '',
                ], 'Fallback customer returned.', 1);
            }

            return $this->sendResponse($customer->toArray(), 'Customer fetched successfully.');
        } catch (Exception $e) {
            return $this->sendError($e->getMessage(), [], 200);
        }
    }

    public function getStockByProductCode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_code' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors()->first(), [], 200);
        }

        try {
            $product = ProductsModel::select('id')
                ->where('code', $request->input('product_code'))
                ->first();

            if (empty($product)) {
                return $this->sendError('Product not found.', [], 200);
            }

            $stock = StockModel::where('product_id', $product->id)
                ->selectRaw('SUM(IFNULL(purchase,0)) as purchase_stock, SUM(IFNULL(sale,0)) as sales_stock, SUM(IFNULL(sale_return,0)) as sale_return_stock')
                ->first();

            $currentStock = 0;
            if (!empty($stock)) {
                $currentStock = ((float) $stock->purchase_stock + (float) $stock->sale_return_stock) - (float) $stock->sales_stock;
            }

            return $this->sendResponse(['stock' => $currentStock], 'Stock fetched successfully.');
        } catch (Exception $e) {
            return $this->sendError($e->getMessage(), [], 200);
        }
    }

    public function getNextBillNo(Request $request)
    {
        try {
            $lastBill = OrderModel::max('bill_id');
            $nextBillNo = intval($lastBill) + 1;
            if ($nextBillNo <= 0) {
                $nextBillNo = 1;
            }
            return $this->sendResponse(['next_bill_no' => $nextBillNo], 'Next bill number fetched successfully.');
        } catch (Exception $e) {
            return $this->sendError($e->getMessage(), [], 200);
        }
    }

    public function savePosOrder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'bill_type' => 'required|integer',
            'customer_id' => 'required|integer',
            'bill_date' => 'required|date',
            'payment_method' => 'required|string',
            'total' => 'required|numeric',
            'created_by' => 'required|integer',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|integer',
            'items.*.category_id' => 'required|integer',
            'items.*.hsn_id' => 'required|integer',
            'items.*.quantity' => 'required|numeric',
            'items.*.quantity_price' => 'required|numeric',
            'items.*.amount' => 'required|numeric',
            'items.*.gst' => 'required|numeric',
            'items.*.tax' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors()->first(), [], 200);
        }

        DB::beginTransaction();
        try {
            $status = 1;
            if (intval($request->input('bill_type')) === 2 || intval($request->input('bill_type')) === 3) {
                $status = 4;
            }

            $order = OrderModel::create([
                'bill_id' => 0,
                'customer_id' => $request->input('customer_id'),
                'type' => $request->input('bill_type'),
                'date' => $request->input('bill_date'),
                'payment_method' => $request->input('payment_method'),
                'created_by' => $request->input('created_by'),
                'order_discount' => 0,
                'total' => $request->input('total'),
                'upi' => $request->input('upi', 0),
                'return_total' => 0,
                'status' => $status,
                'return_status' => '',
                'damage_status' => '',
            ]);

            $billId = intval($order->id);
            OrderModel::where('id', $order->id)->update(['bill_id' => $billId]);

            foreach ($request->input('items') as $item) {
                OrderDetailModel::create([
                    'order_id' => $billId,
                    'product_id' => $item['product_id'],
                    'category_id' => $item['category_id'],
                    'quantity' => $item['quantity'],
                    'free_item' => 0,
                    'quantity_price' => $item['quantity_price'],
                    'amount' => $item['amount'],
                    'gst' => $item['gst'],
                    'tax' => $item['tax'],
                    'discount' => 0,
                    'status' => 1,
                ]);

                StockModel::create([
                    'type' => 'sale',
                    'bill' => $billId,
                    'date' => $request->input('bill_date'),
                    'product_id' => $item['product_id'],
                    'category_id' => $item['category_id'],
                    'hsn_id' => $item['hsn_id'],
                    'sale' => $item['quantity'],
                    'purchase' => 0,
                    'sale_return' => 0,
                    'purchase_return' => 0,
                    'purchase_free' => 0,
                    'sale_free' => 0,
                    'sale_damage' => 0,
                    'purchase_damage' => 0,
                    'purchase_damage_return' => 0,
                    'sale_damage_return' => 0,
                ]);
            }

            DB::commit();
            return $this->sendResponse([
                'order_id' => $order->id,
                'bill_id' => (string) $billId,
            ], 'Order saved successfully.');
        } catch (Exception $e) {
            DB::rollBack();
            return $this->sendError($e->getMessage(), [], 200);
        }
    }

    public function getDailySalesReport(Request $request)
    {
        try {
            $rows = DB::table('order_details as od')
                ->join('order as o', 'o.bill_id', '=', 'od.order_id')
                ->leftJoin('products as p', 'p.id', '=', 'od.product_id')
                ->whereDate('o.date', Carbon::today()->toDateString())
                ->where('od.status', 1)
                ->selectRaw('COALESCE(p.name, od.product_id) as ProductName, SUM(od.quantity) as Quantity, SUM(od.amount) as TotalAmount')
                ->groupBy('ProductName')
                ->orderBy('ProductName')
                ->get();

            return $this->sendResponse(['rows' => $rows], 'Daily sales report fetched successfully.', 1);
        } catch (Exception $e) {
            return $this->sendError($e->getMessage(), [], 200);
        }
    }

    public function getDatewiseProductSalesReport(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'from_date' => 'required|date',
            'to_date' => 'required|date',
        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->errors()->first(), [], 200);
        }

        try {
            $rows = DB::table('order_details as od')
                ->join('order as o', 'o.bill_id', '=', 'od.order_id')
                ->leftJoin('products as p', 'p.id', '=', 'od.product_id')
                ->whereBetween('o.date', [$request->input('from_date'), $request->input('to_date')])
                ->where('od.status', 1)
                ->selectRaw('COALESCE(p.name, od.product_id) as ProductName, SUM(od.quantity) as Quantity, SUM(od.amount) as TotalAmount')
                ->groupBy('ProductName')
                ->orderBy('ProductName')
                ->get();

            return $this->sendResponse(['rows' => $rows], 'Datewise product sales report fetched successfully.', 1);
        } catch (Exception $e) {
            return $this->sendError($e->getMessage(), [], 200);
        }
    }

    public function getTaxReport(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'from_date' => 'required|date',
            'to_date' => 'required|date',
        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->errors()->first(), [], 200);
        }

        try {
            $rows = DB::table('order as o')
                ->leftJoin('order_details as od', 'od.order_id', '=', 'o.bill_id')
                ->whereBetween('o.date', [$request->input('from_date'), $request->input('to_date')])
                ->groupBy('o.bill_id', 'o.date', 'o.total')
                ->orderBy('o.bill_id', 'desc')
                ->selectRaw('o.bill_id as bill_id, DATE(o.date) as bill_date, o.total as bill_amount, SUM(od.amount - (od.amount * (100 / (100 + IFNULL(od.gst,0))))) as bill_taxamount, o.total as bill_fullamount')
                ->get();

            return $this->sendResponse(['rows' => $rows], 'Tax report fetched successfully.', 1);
        } catch (Exception $e) {
            return $this->sendError($e->getMessage(), [], 200);
        }
    }

    public function getOrderByBillNo(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'bill_no' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->errors()->first(), [], 200);
        }

        try {
            $order = OrderModel::where('bill_id', $request->input('bill_no'))->first();
            if (empty($order)) {
                return $this->sendError('Bill not found.', [], 200);
            }

            $customer = CustomerModel::where('id', $order->customer_id)->first();
            $itemRows = DB::table('order_details as od')
                ->leftJoin('products as p', 'p.id', '=', 'od.product_id')
                ->where('od.order_id', $order->bill_id)
                ->where('od.status', 1)
                ->select(
                    'od.product_id',
                    'od.quantity',
                    'od.quantity_price as rate',
                    'od.amount',
                    'od.gst',
                    'p.code as product_code',
                    'p.name as product_name',
                    'p.tamil_name as product_tamil_name'
                )
                ->get();

            $items = [];
            $i = 1;
            foreach ($itemRows as $row) {
                $items[] = [
                    'item_no' => $i++,
                    'product_code' => (string)($row->product_code ?? ''),
                    'product_name' => (string)($row->product_name ?? ''),
                    'product_tamil_name' => (string)($row->product_tamil_name ?? ''),
                    'rate' => (float)$row->rate,
                    'quantity' => (float)$row->quantity,
                    'amount' => (float)$row->amount,
                    'gst' => (float)$row->gst,
                ];
            }

            return $this->sendResponse([
                'bill_id' => (string)$order->bill_id,
                'bill_date' => (string)$order->date,
                'total' => (float)$order->total,
                'payment_method' => (string)$order->payment_method,
                'upi' => (float)$order->upi,
                'type' => (int)$order->type,
                'status' => (int)$order->status,
                'customer_name' => (string)($customer->name ?? ''),
                'customer_mobile' => (string)($customer->mobile ?? ''),
                'customer_address' => (string)($customer->address ?? ''),
                'items' => $items,
            ], 'Bill fetched successfully.', 1);
        } catch (Exception $e) {
            return $this->sendError($e->getMessage(), [], 200);
        }
    }

    public function deleteOrderByBillNo(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'bill_no' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->errors()->first(), [], 200);
        }

        DB::beginTransaction();
        try {
            $order = OrderModel::where('bill_id', $request->input('bill_no'))->first();
            if (empty($order)) {
                DB::rollBack();
                return $this->sendError('Bill not found.', [], 200);
            }

            StockModel::where('bill', $order->bill_id)->delete();
            OrderDetailModel::where('order_id', $order->bill_id)->delete();
            OrderModel::where('id', $order->id)->delete();

            DB::commit();
            return $this->sendResponse([], 'Bill deleted successfully.', 1);
        } catch (Exception $e) {
            DB::rollBack();
            return $this->sendError($e->getMessage(), [], 200);
        }
    }
}
