<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #111; }
        h1 { font-size: 16px; text-align: center; margin: 0 0 4px; }
        .note { font-size: 9px; color: #555; text-align: center; margin-bottom: 14px; }
        .qr-box { float: right; width: 90px; height: 90px; border: 1px dashed #999; text-align: center; font-size: 8px; padding-top: 36px; }
        .clear { clear: both; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ccc; padding: 4px 5px; }
        th { background: #f3f3f3; font-size: 10px; }
        td.num { text-align: right; }
        .totals { margin-top: 10px; text-align: right; font-size: 11px; }
    </style>
</head>
<body>
@php
    use App\Services\Gst\GstDocumentQuery;
    use App\Services\Gst\GstTaxCalculator;

    $fmtMoney = function ($n) {
        return '₹' . number_format((float) $n, 2);
    };
    $fmtDate = function ($iso) {
        if (!$iso) return '-';
        $p = explode('-', $iso);
        return count($p) === 3 ? "{$p[2]}-{$p[1]}-{$p[0]}" : $iso;
    };

    $sumTaxable = 0; $sumTax = 0; $sumTotal = 0;
    foreach ($doc['lines'] as $line) {
        $sumTaxable += GstTaxCalculator::lineTaxable($line);
        $sumTax += GstTaxCalculator::lineTaxAmount($line);
        $sumTotal += GstTaxCalculator::parseNum($line['amount'] ?? 0);
    }
@endphp

<div class="qr-box">IRN / QR<br>(via GSP)</div>
<h1>TAX INVOICE (E-invoice preparation)</h1>
<p class="note">For records and JSON upload prep. Statutory IRN must be generated on the IRP via your GSP.</p>
<div class="clear"></div>

<p><strong>Seller:</strong> {{ $company['company_name'] ?? '' }}</p>
@if(!empty($company['company_gstin']))<p><strong>GSTIN:</strong> {{ $company['company_gstin'] }}</p>@endif
@if(!empty($company['company_address']))<p><strong>Address:</strong> {{ $company['company_address'] }}</p>@endif

<p><strong>Buyer:</strong> {{ $doc['party_name'] ?? '-' }}</p>
@if(!empty($doc['party_gstin']))<p><strong>GSTIN:</strong> {{ $doc['party_gstin'] }}</p>@endif
@if(!empty($doc['party_address']))<p><strong>Address:</strong> {{ $doc['party_address'] }}</p>@endif

<p><strong>Invoice no.:</strong> {{ $doc['bill_id'] ?? '' }} &nbsp; <strong>Date:</strong> {{ $fmtDate($doc['bill_date'] ?? '') }}</p>
<p><strong>Document type:</strong> {{ $doc['supply_label'] ?? '' }}</p>

<table>
    <thead>
        <tr>
            <th>Product</th>
            <th>HSN</th>
            <th>Qty</th>
            <th class="num">Taxable</th>
            <th class="num">GST%</th>
            <th class="num">Tax</th>
            <th class="num">Total</th>
        </tr>
    </thead>
    <tbody>
        @foreach($doc['lines'] as $line)
        @php
            $taxable = GstTaxCalculator::lineTaxable($line);
            $tax = GstTaxCalculator::lineTaxAmount($line);
            $total = GstTaxCalculator::parseNum($line['amount'] ?? 0);
            $qty = trim(($line['quantity'] ?? '') . ' ' . ($line['unit_name'] ?? ''));
        @endphp
        <tr>
            <td>{{ GstDocumentQuery::displayProductName($line) }}</td>
            <td>{{ $line['hsn_code'] ?? '' }}</td>
            <td>{{ $qty ?: '-' }}</td>
            <td class="num">{{ number_format($taxable, 2) }}</td>
            <td class="num">{{ $line['gst'] ?? '' }}</td>
            <td class="num">{{ number_format($tax, 2) }}</td>
            <td class="num">{{ number_format($total, 2) }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
<p class="totals">
    <strong>Totals</strong> — Taxable {{ $fmtMoney($sumTaxable) }} &nbsp; Tax {{ $fmtMoney($sumTax) }}<br>
    Lines {{ $fmtMoney($sumTotal) }} &nbsp; Bill {{ $fmtMoney($doc['total'] ?? 0) }}
</p>
</body>
</html>
