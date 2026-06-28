<?php

namespace App\Exports;

use App\Models\OrderDetailModel;
use App\Models\PurchaseOrderDetailModel; // Import the Purchase model
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class GSTR1Export implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    private $startDate;
    private $endDate;
    private $reportType; // New property to store the report type

    private const COMPANY_STATE_CODE = '33';

    // Updated constructor to accept the report type
    public function __construct(string $startDate, string $endDate, string $reportType)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->reportType = $reportType;
    }

    /**
    * This method now dynamically chooses the query based on the report type.
    */
    public function query()
    {
        if ($this->reportType === 'purchase') {
            // --- NEW PURCHASE QUERY ---
            // Assumes a PurchaseOrderDetailModel exists and tables are named 'purchase_order' and 'vendor'
            return PurchaseOrderDetailModel::query()
                ->select(
                    'purchase_order.bill_id', // Using internal bill_id for consistency
                    'purchase_order.date',
                    'purchase_order.total as invoice_value',
                    'vendor.name as party_name', // Using a generic alias 'party_name'
                    'vendor.gst as party_gstin',   // Using a generic alias 'party_gstin'
                    'products.name as product_name',
                    'products.hsn as hsn_code',
                    'purchase_order_details.quantity',
                    'purchase_order_details.amount as taxable_value',
                    'purchase_order_details.gst as gst_rate'
                )
                ->leftJoin('purchase_order', 'purchase_order_details.order_id', '=', 'purchase_order.id')
                ->leftJoin('vendor', 'purchase_order.customer_id', '=', 'vendor.id') // Join with vendor table
                ->leftJoin('products', 'purchase_order_details.product_id', '=', 'products.id')
                ->whereBetween('purchase_order.date', [$this->startDate, $this->endDate])
                ->whereNotNull('vendor.gst')
                ->where('vendor.gst', '!=', '');
        }

        // --- EXISTING SALES QUERY (Default) ---
        return OrderDetailModel::query()
            ->select(
                'order.bill_id',
                'order.date',
                'order.total as invoice_value',
                'customer.name as party_name', // Using a generic alias 'party_name'
                'customer.gst as party_gstin',   // Using a generic alias 'party_gstin'
                'products.name as product_name',
                'products.hsn as hsn_code',
                'order_details.quantity',
                'order_details.amount as taxable_value',
                'order_details.gst as gst_rate'
            )
            ->leftJoin('order', 'order_details.order_id', '=', 'order.id')
            ->leftJoin('customer', 'order.customer_id', '=', 'customer.id')
            ->leftJoin('products', 'order_details.product_id', '=', 'products.id')
            ->whereBetween('order.date', [$this->startDate, $this->endDate])
            ->whereNotNull('customer.gst')
            ->where('customer.gst', '!=', '');
    }

    /**
    * This method now dynamically sets the header based on the report type.
    */
    public function headings(): array
    {
        $firstColumn = ($this->reportType === 'purchase') ? 'Vendor GSTIN' : 'Customer GSTIN';

        return [
            $firstColumn,
            'Invoice Number',
            'Invoice Date',
            'Invoice Value',
            'Place of Supply',
            'Reverse Charge',
            'Invoice Type',
            'HSN Code',
            'HSN Description',
            'GST Rate (%)',
            'Taxable Value',
            'Central Tax',
            'State/UT Tax',
            'Integrated Tax',
            'Cess Amount',
            'Total Tax Amount',
            'Total Amount (incl. Tax)'
        ];
    }

    /**
    * This method works for both sales and purchase because we used generic aliases.
    */
    public function map($detail): array
    {
        $partyGstin = $detail->party_gstin; // Using generic alias
        $placeOfSupply = (empty($partyGstin) || strlen($partyGstin) < 2) ? '' : substr($partyGstin, 0, 2);

        $taxableValue = (float)$detail->taxable_value;
        $gstRate = (float)$detail->gst_rate;
        $gstAmount = ($taxableValue * $gstRate) / 100;

        $centralTax = 0;
        $stateTax = 0;
        $integratedTax = 0;

        if ($placeOfSupply == self::COMPANY_STATE_CODE) {
            $centralTax = $gstAmount / 2;
            $stateTax = $gstAmount / 2;
        } else {
            $integratedTax = $gstAmount;
        }

        return [
            $partyGstin,
            $detail->bill_id,
            date('d-m-Y', strtotime($detail->date)),
            (float)$detail->invoice_value,
            $placeOfSupply,
            'N',
            'R',
            $detail->hsn_code,
            $detail->product_name,
            $gstRate,
            $taxableValue,
            round($centralTax, 2),
            round($stateTax, 2),
            round($integratedTax, 2),
            0, // Cess Amount
            round($gstAmount, 2),
            $taxableValue + round($gstAmount, 2),
        ];
    }
}