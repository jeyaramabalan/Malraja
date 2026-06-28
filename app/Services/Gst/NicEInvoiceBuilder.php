<?php

namespace App\Services\Gst;

class NicEInvoiceBuilder
{
    public static function build(array $doc, array $company, string $companyState): array
    {
        $sellerGstin = GstCompanyProfile::normGstin($company['company_gstin'] ?? '');
        $buyerGstin = GstCompanyProfile::normGstin($doc['party_gstin'] ?? '');
        $buyerSt = GstCompanyProfile::stateCode($buyerGstin) ?: $companyState;

        $seller = self::partyBlock($company['company_name'] ?? '', $sellerGstin, $company['company_address'] ?? '', $companyState);
        $buyer = array_merge(
            self::partyBlock($doc['party_name'] ?? '', $buyerGstin, $doc['party_address'] ?? '', $companyState),
            ['Pos' => $buyerSt]
        );

        $itemList = [];
        $assVal = 0.0;
        $cgstVal = 0.0;
        $sgstVal = 0.0;
        $igstVal = 0.0;

        foreach ($doc['lines'] as $idx => $line) {
            $line['_party_gstin'] = $buyerGstin;
            $qty = GstTaxCalculator::parseNum($line['quantity'] ?? 0);
            $tax = GstTaxCalculator::splitLineTax($line, $companyState, $buyerGstin);
            $unitPrice = $qty > 0 ? GstTaxCalculator::round2($tax['taxable'] / $qty) : GstTaxCalculator::round2($tax['taxable']);

            $assVal += $tax['taxable'];
            $cgstVal += $tax['cgst'];
            $sgstVal += $tax['sgst'];
            $igstVal += $tax['igst'];

            $itemList[] = [
                'SlNo' => (string) ($idx + 1),
                'PrdDesc' => mb_substr(GstDocumentQuery::displayProductName($line), 0, 300),
                'IsServc' => 'N',
                'HsnCd' => self::hsnDigits($line['hsn_code'] ?? ''),
                'Qty' => $qty,
                'Unit' => self::mapUnitCode($line['unit_name'] ?? ''),
                'UnitPrice' => $unitPrice,
                'TotAmt' => GstTaxCalculator::round2($tax['taxable']),
                'Discount' => 0,
                'AssAmt' => GstTaxCalculator::round2($tax['taxable']),
                'GstRt' => $tax['gst_rate'],
                'CgstAmt' => $tax['cgst'],
                'SgstAmt' => $tax['sgst'],
                'IgstAmt' => $tax['igst'],
                'TotItemVal' => $tax['total'],
            ];
        }

        $totInvVal = GstTaxCalculator::round2($assVal + $cgstVal + $sgstVal + $igstVal);

        return [
            'Version' => '1.1',
            'TranDtls' => [
                'TaxSch' => 'GST',
                'SupTyp' => 'B2B',
                'RegRev' => 'N',
                'IgstOnIntra' => 'N',
            ],
            'DocDtls' => [
                'Typ' => 'INV',
                'No' => (string) $doc['bill_id'],
                'Dt' => self::fmtDateDmy($doc['bill_date'] ?? ''),
            ],
            'SellerDtls' => $seller,
            'BuyerDtls' => $buyer,
            'ItemList' => $itemList,
            'ValDtls' => [
                'AssVal' => GstTaxCalculator::round2($assVal),
                'CgstVal' => GstTaxCalculator::round2($cgstVal),
                'SgstVal' => GstTaxCalculator::round2($sgstVal),
                'IgstVal' => GstTaxCalculator::round2($igstVal),
                'TotInvVal' => $totInvVal,
            ],
            '_meta' => [
                'doc_type' => $doc['doc_type'],
                'doc_id' => $doc['id'],
                'supply_label' => $doc['supply_label'] ?? '',
                'note' => 'Preparation JSON only — generate IRN on the IRP via your GSP when required.',
            ],
        ];
    }

    private static function partyBlock(string $name, string $gstin, string $address, string $companyState): array
    {
        $stcd = GstCompanyProfile::stateCode($gstin) ?: $companyState;

        return [
            'Gstin' => GstCompanyProfile::normGstin($gstin),
            'LglNm' => mb_substr(trim($name), 0, 100),
            'Addr1' => mb_substr(trim($address), 0, 100),
            'Loc' => mb_substr(trim($address), 0, 50),
            'Stcd' => $stcd,
        ];
    }

    private static function hsnDigits($hsn): string
    {
        $digits = preg_replace('/\D/', '', (string) $hsn);

        return $digits !== '' ? substr($digits, 0, 8) : '0';
    }

    private static function mapUnitCode(string $unit): string
    {
        $u = strtoupper(trim($unit));
        $map = [
            'NOS' => 'NOS', 'NO' => 'NOS', 'PCS' => 'PCS', 'PC' => 'PCS',
            'KGS' => 'KGS', 'KG' => 'KGS', 'LTR' => 'LTR', 'L' => 'LTR',
            'BOX' => 'BOX', 'BAG' => 'BAG', 'MTS' => 'MTR', 'MTR' => 'MTR',
        ];
        if (isset($map[$u])) {
            return $map[$u];
        }
        if (strlen($u) >= 3) {
            return substr($u, 0, 3);
        }

        return 'NOS';
    }

    private static function fmtDateDmy(string $isoDate): string
    {
        $d = GstDateRange::sliceDate($isoDate);
        if ($d === '') {
            return '';
        }
        $parts = explode('-', $d);

        return count($parts) === 3 ? "{$parts[2]}/{$parts[1]}/{$parts[0]}" : '';
    }
}
