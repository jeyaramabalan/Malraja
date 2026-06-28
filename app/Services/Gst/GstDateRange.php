<?php

namespace App\Services\Gst;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class GstDateRange
{
    public static function resolve(array $opts): ?array
    {
        $scope = strtolower((string) ($opts['scope'] ?? 'day'));
        if ($scope === 'bill') {
            return null;
        }

        $date = self::sliceDate($opts['date'] ?? null) ?: Carbon::today()->toDateString();

        if ($scope === 'day') {
            return ['from' => $date, 'to' => $date];
        }

        if ($scope === 'week') {
            $d = Carbon::parse($date);
            $mon = $d->copy()->startOfWeek(Carbon::MONDAY);
            $sun = $mon->copy()->addDays(6);

            return ['from' => $mon->toDateString(), 'to' => $sun->toDateString()];
        }

        if ($scope === 'month') {
            $month = (string) ($opts['month'] ?? substr($date, 0, 7));
            if (!preg_match('/^\d{4}-\d{2}$/', $month)) {
                throw new \InvalidArgumentException('month must be YYYY-MM');
            }
            $start = Carbon::createFromFormat('Y-m-d', $month . '-01');
            $end = $start->copy()->endOfMonth();

            return ['from' => $start->toDateString(), 'to' => $end->toDateString()];
        }

        throw new \InvalidArgumentException('scope must be bill, day, week, or month');
    }

    public static function sliceDate($value): string
    {
        $s = trim((string) $value);
        if (preg_match('/^(\d{4}-\d{2}-\d{2})/', $s, $m)) {
            return $m[1];
        }

        return '';
    }

    public static function parseDocTypes(?string $typesParam, array $allowed): array
    {
        if (!$typesParam || $typesParam === 'all') {
            return $allowed;
        }

        $picked = array_values(array_filter(array_map('trim', explode(',', strtolower($typesParam)))));

        return array_values(array_intersect($picked, $allowed));
    }

    public static function parseSelected(?string $selectedParam): array
    {
        if (!$selectedParam) {
            return [];
        }

        $out = [];
        foreach (explode(',', $selectedParam) as $part) {
            $part = trim($part);
            if ($part === '') {
                continue;
            }
            [$docType, $id] = array_pad(explode(':', $part, 2), 2, null);
            $docType = strtolower((string) $docType);
            $id = (int) $id;
            if ($id > 0 && in_array($docType, ['delivery', 'retail', 'purchase'], true)) {
                $out[] = ['doc_type' => $docType, 'id' => $id];
            }
        }

        return $out;
    }
}
