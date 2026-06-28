<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Exports\GSTR1Export;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Log;

class GSTR1Controller extends Controller
{
    private const COMPANY_STATE_CODE = '33';

    public function index()
    {
        return view('reports.gst_r1_report_page');
    }

    public function exportExcel(Request $request)
    {
        set_time_limit(0);
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $reportType = $request->input('type', 'sales'); // Get the report type, default to 'sales'

        if (empty($startDate) || empty($endDate)) {
            return redirect()->back()->with('error', 'Please select a valid date range to generate the report.');
        }

        $reportName = ($reportType === 'purchase') ? 'gst_purchase_report' : 'gst_sales_report';
        $filename = $reportName . '_' . $startDate . '_to_' . $endDate . '.xlsx';

        // Pass all three parameters to the GSTR1Export class
        return Excel::download(new GSTR1Export($startDate, $endDate, $reportType), $filename);
    }

    public function exportJson(Request $request)
    {
        set_time_limit(0);
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $reportType = $request->input('type', 'sales');
        
        if (empty($startDate) || empty($endDate)) {
            return response()->json(['error' => 'Please select a valid date range.'], 400);
        }

        $reportTitle = ($reportType === 'purchase') ? 'GST B2B Inward Supplies' : 'GST B2B Outward Supplies';
        $jsonOutput = [
            'reportType' => $reportTitle,
            'generationDate' => date('Y-m-d H:i:s'),
            'period' => date('m-Y', strtotime($startDate)),
            'dateRange' => "$startDate to $endDate",
            'data' => []
        ];

        $groupedData = [];

        // Build the base query
        if ($reportType === 'purchase') {
            $query = \App\Models\PurchaseOrderDetailModel::query()
                ->select(
                    'purchase_order.bill_id', 'purchase_order.date', 'purchase_order.total as invoice_value',
                    'vendor.name as party_name', 'vendor.gst as party_gstin',
                    'products.name as product_name', 'products.hsn as hsn_code',
                    'purchase_order_details.quantity', 'purchase_order_details.amount as taxable_value', 'purchase_order_details.gst as gst_rate'
                )
                ->leftJoin('purchase_order', 'purchase_order_details.order_id', '=', 'purchase_order.id')
                ->leftJoin('vendor', 'purchase_order.customer_id', '=', 'vendor.id')
                ->leftJoin('products', 'purchase_order_details.product_id', '=', 'products.id')
                ->whereBetween('purchase_order.date', [$startDate, $endDate])
                ->whereNotNull('vendor.gst')->where('vendor.gst', '!=', '');
        } else {
            $query = \App\Models\OrderDetailModel::query()
                ->select(
                    'order.bill_id', 'order.date', 'order.total as invoice_value',
                    'customer.name as party_name', 'customer.gst as party_gstin',
                    'products.name as product_name', 'products.hsn as hsn_code',
                    'order_details.quantity', 'order_details.amount as taxable_value', 'order_details.gst as gst_rate'
                )
                ->leftJoin('order', 'order_details.order_id', '=', 'order.id')
                ->leftJoin('customer', 'order.customer_id', '=', 'customer.id')
                ->leftJoin('products', 'order_details.product_id', '=', 'products.id')
                ->whereBetween('order.date', [$startDate, $endDate])
                ->whereNotNull('customer.gst')->where('customer.gst', '!=', '');
        }

        // Chunk the results of the chosen query
        $query->chunk(200, function ($chunk) use (&$groupedData) {
            foreach ($chunk as $detail) {
                $ctin = $detail->party_gstin;
                $inum = $detail->bill_id;

                if (!isset($groupedData[$ctin])) {
                    $groupedData[$ctin] = [ 'ctin' => $ctin, 'invoices' => [] ];
                }

                if (!isset($groupedData[$ctin]['invoices'][$inum])) {
                    $groupedData[$ctin]['invoices'][$inum] = [
                        'inum' => $inum, 'idt' => date('d-m-Y', strtotime($detail->date)),
                        'val' => (float)$detail->invoice_value,
                        'pos' => (empty($ctin) || strlen($ctin) < 2) ? '' : substr($ctin, 0, 2),
                        'rchrg' => 'N', 'inv_typ' => 'R', 'items' => []
                    ];
                }
                $groupedData[$ctin]['invoices'][$inum]['items'][] = $this->mapItem($detail);
            }
        });

        $finalData = [];
        foreach ($groupedData as $ctinData) {
            $ctinData['invoices'] = array_values($ctinData['invoices']);
            $finalData[] = $ctinData;
        }
        $jsonOutput['data'] = $finalData;

        $reportName = ($reportType === 'purchase') ? 'gst_purchase_report' : 'gst_sales_report';
        $filename = $reportName . '_' . $startDate . '_to_' . $endDate . '.json';
        $headers = [
            'Content-type'        => 'application/json; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        return response()->json($jsonOutput, 200, $headers, JSON_PRETTY_PRINT);
    }
    
    private function mapItem($detail) {
        $taxableValue = (float)$detail->taxable_value;
        $gstRate = (float)$detail->gst_rate;
        $gstAmount = ($taxableValue * $gstRate) / 100;
        $centralTax = 0; $stateTax = 0; $integratedTax = 0;
        $placeOfSupply = (empty($detail->party_gstin) || strlen($detail->party_gstin) < 2) ? '' : substr($detail->party_gstin, 0, 2);
        
        if ($placeOfSupply == GSTR1Controller::COMPANY_STATE_CODE) {
            $centralTax = $gstAmount / 2;
            $stateTax = $gstAmount / 2;
        } else {
            $integratedTax = $gstAmount;
        }

        return [
            'hsn_code' => $detail->hsn_code, 'hsn_description' => $detail->product_name,
            'gst_rate' => $gstRate, 'taxable_value' => $taxableValue,
            'central_tax' => round($centralTax, 2), 'state_tax' => round($stateTax, 2),
            'integrated_tax' => round($integratedTax, 2), 'cess_amount' => 0,
            'total_tax_amount' => round($gstAmount, 2),
            'total_amount_with_tax' => $taxableValue + round($gstAmount, 2),
        ];
    }
}