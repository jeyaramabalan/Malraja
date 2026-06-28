<?php

namespace App\Services\Gst;

class GstEligibility
{
    public static function assessEway(object $row): array
    {
        $skip = self::commonSkip($row);
        $total = GstTaxCalculator::parseNum($row->total ?? 0);
        if ($total < (float) config('gst.eway_min_value')) {
            $skip[] = 'under_threshold';
        }

        return ['eligible' => count($skip) === 0, 'skip_reasons' => $skip];
    }

    public static function assessEinvoice(object $row): array
    {
        $skip = self::commonSkip($row);

        return ['eligible' => count($skip) === 0, 'skip_reasons' => $skip];
    }

    private static function commonSkip(object $row): array
    {
        $skip = [];
        $gstin = GstCompanyProfile::normGstin($row->party_gstin ?? '');
        if ($gstin === '') {
            $skip[] = 'no_gstin';
        }
        if ((int) ($row->return_status ?? 0) !== 0) {
            $skip[] = 'returned';
        }

        return $skip;
    }

    public static function mapPreviewRow(object $row, callable $assessor): array
    {
        $result = $assessor($row);

        return [
            'doc_type' => $row->doc_type,
            'id' => (int) $row->id,
            'bill_id' => (string) $row->bill_id,
            'bill_date' => GstDateRange::sliceDate($row->bill_date ?? ''),
            'party_name' => (string) ($row->party_name ?? ''),
            'party_gstin' => GstCompanyProfile::normGstin($row->party_gstin ?? ''),
            'party_address' => (string) ($row->party_address ?? ''),
            'total' => GstTaxCalculator::parseNum($row->total ?? 0),
            'eligible' => $result['eligible'],
            'skip_reasons' => $result['skip_reasons'],
        ];
    }

    public static function parseTransport(array $query): array
    {
        return [
            'transporter_name' => trim((string) ($query['transporter_name'] ?? '')),
            'transporter_gstin' => GstCompanyProfile::normGstin($query['transporter_gstin'] ?? ''),
            'vehicle_no' => strtoupper(trim((string) ($query['vehicle_no'] ?? ''))),
            'transport_mode' => strtolower(trim((string) ($query['transport_mode'] ?? 'road'))) ?: 'road',
            'distance_km' => trim((string) ($query['distance_km'] ?? '')),
        ];
    }

    public static function safeFilename(string $billId): string
    {
        return preg_replace('/[^\w.-]+/', '_', $billId) ?: 'bill';
    }
}
