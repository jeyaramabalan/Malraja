<?php

namespace App\Services\Gst;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class GstCompanyProfile
{
    public static function normGstin(?string $gstin): string
    {
        return strtoupper(preg_replace('/\s+/', '', (string) $gstin));
    }

    public static function stateCode(?string $gstin): string
    {
        $g = self::normGstin($gstin);

        return strlen($g) >= 2 ? substr($g, 0, 2) : '';
    }

    public static function get(): array
    {
        $profile = [
            'company_name' => config('gst.company_name'),
            'company_gstin' => self::normGstin(config('gst.company_gstin')),
            'company_address' => config('gst.company_address'),
            'company_phone' => config('gst.company_phone'),
        ];

        if (Schema::hasTable('settings')) {
            $row = DB::table('settings')->first();
            if ($row) {
                if (!empty($row->company_name ?? null)) {
                    $profile['company_name'] = $row->company_name;
                }
                if (!empty($row->company_address ?? null)) {
                    $profile['company_address'] = $row->company_address;
                }
                if (!empty($row->company_phone ?? null)) {
                    $profile['company_phone'] = $row->company_phone;
                }
                if (!empty($row->company_gstin ?? null)) {
                    $profile['company_gstin'] = self::normGstin($row->company_gstin);
                } elseif (!empty($row->gstin ?? null)) {
                    $profile['company_gstin'] = self::normGstin($row->gstin);
                }
            }
        }

        $profile['state_code'] = self::stateCode($profile['company_gstin']);

        return $profile;
    }
}
