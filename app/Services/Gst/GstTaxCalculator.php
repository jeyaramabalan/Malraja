<?php

namespace App\Services\Gst;

class GstTaxCalculator
{
    public static function round2(float $n): float
    {
        return round($n, 2);
    }

    public static function parseNum($value): float
    {
        if ($value === null || $value === '') {
            return 0.0;
        }
        $n = (float) str_replace(',', '', (string) $value);

        return is_finite($n) ? $n : 0.0;
    }

    /** Taxable value from a line (amount is line total incl. tax when configured). */
    public static function lineTaxable(array $line): float
    {
        $lineTotal = self::parseNum($line['amount'] ?? 0);
        if ($lineTotal <= 0) {
            $qty = self::parseNum($line['quantity'] ?? 0);
            $rate = self::parseNum($line['quantity_price'] ?? 0);
            $lineTotal = $qty * $rate;
        }

        $gstPct = self::parseNum($line['gst'] ?? 0);

        if (config('gst.amount_includes_gst') && $gstPct > 0) {
            return self::round2($lineTotal / (1 + $gstPct / 100));
        }

        return self::round2($lineTotal);
    }

    public static function lineTaxAmount(array $line): float
    {
        $lineTotal = self::parseNum($line['amount'] ?? 0);
        if ($lineTotal <= 0) {
            $qty = self::parseNum($line['quantity'] ?? 0);
            $rate = self::parseNum($line['quantity_price'] ?? 0);
            $lineTotal = $qty * $rate;
        }

        $taxable = self::lineTaxable($line);
        $extra = self::parseNum($line['tax'] ?? 0);

        if (config('gst.amount_includes_gst')) {
            return self::round2($lineTotal - $taxable + $extra);
        }

        $gstPct = self::parseNum($line['gst'] ?? 0);

        return self::round2($taxable * $gstPct / 100 + $extra);
    }

    public static function splitLineTax(array $line, string $companyState, ?string $partyGstin = null): array
    {
        $taxable = self::lineTaxable($line);
        $gstRate = self::round2(self::parseNum($line['gst'] ?? 0));
        $tax = self::lineTaxAmount($line);
        $buyerSt = GstCompanyProfile::stateCode($partyGstin ?? ($line['_party_gstin'] ?? ''));
        $intra = $buyerSt !== '' && $buyerSt === $companyState;

        if ($intra) {
            return [
                'taxable' => $taxable,
                'gst_rate' => $gstRate,
                'cgst' => self::round2($tax / 2),
                'sgst' => self::round2($tax / 2),
                'igst' => 0.0,
                'total' => self::round2($taxable + $tax),
            ];
        }

        return [
            'taxable' => $taxable,
            'gst_rate' => $gstRate,
            'cgst' => 0.0,
            'sgst' => 0.0,
            'igst' => self::round2($tax),
            'total' => self::round2($taxable + $tax),
        ];
    }
}
