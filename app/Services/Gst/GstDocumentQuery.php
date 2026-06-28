<?php

namespace App\Services\Gst;

use Illuminate\Support\Facades\DB;

class GstDocumentQuery
{
    private const LINE_SELECT = '
        d.quantity,
        d.quantity_price,
        d.amount,
        d.gst,
        d.tax,
        d.product_id,
        p.name AS product_name,
        p.tamil_name AS product_tamilname,
        IFNULL(h.hsn, "") AS hsn_code,
        IFNULL(u.unit, "") AS unit_name
    ';

    public static function listHeadersInRange(string $from, string $to, array $types): array
    {
        $rows = [];
        if (in_array('delivery', $types, true)) {
            $rows = array_merge($rows, self::listDeliveryInRange($from, $to));
        }
        if (in_array('retail', $types, true)) {
            $rows = array_merge($rows, self::listRetailInRange($from, $to));
        }
        if (in_array('purchase', $types, true)) {
            $rows = array_merge($rows, self::listPurchaseInRange($from, $to));
        }

        usort($rows, function ($a, $b) {
            $da = GstDateRange::sliceDate($a->bill_date ?? '');
            $db = GstDateRange::sliceDate($b->bill_date ?? '');
            if ($da !== $db) {
                return strcmp($da, $db);
            }

            return strcmp((string) $a->bill_id, (string) $b->bill_id);
        });

        return $rows;
    }

    public static function getDocument(string $docType, int $id): ?array
    {
        if ($docType === 'delivery') {
            return self::getDeliveryDoc($id);
        }
        if ($docType === 'retail') {
            return self::getRetailDoc($id);
        }
        if ($docType === 'purchase') {
            return self::getPurchaseDoc($id);
        }

        throw new \InvalidArgumentException('docType must be delivery, retail, or purchase');
    }

    public static function findDocIdByBillNo(string $docType, string $billNo): ?int
    {
        $billNo = trim($billNo);
        if ($billNo === '') {
            return null;
        }

        if ($docType === 'delivery') {
            $row = DB::table('order')->where('bill_id', $billNo)->value('id');
        } elseif ($docType === 'retail') {
            $row = DB::table('retail')->where('bill_id', $billNo)->value('id');
        } elseif ($docType === 'purchase') {
            $row = DB::table('purchase_order')->where('bill_id', $billNo)->value('id');
        } else {
            return null;
        }

        return $row ? (int) $row : null;
    }

    private static function listDeliveryInRange(string $from, string $to): array
    {
        return DB::select(
            "SELECT 'delivery' AS doc_type, o.id, o.bill_id, LEFT(o.date, 10) AS bill_date, o.total, o.return_status,
                    c.name AS party_name, TRIM(IFNULL(c.gst, '')) AS party_gstin, c.address AS party_address
             FROM `order` o
             LEFT JOIN customer c ON c.id = o.customer_id
             WHERE LEFT(o.date, 10) >= ? AND LEFT(o.date, 10) <= ?
             ORDER BY o.date ASC, o.bill_id ASC",
            [$from, $to]
        );
    }

    private static function listRetailInRange(string $from, string $to): array
    {
        return DB::select(
            "SELECT 'retail' AS doc_type, r.id, r.bill_id, LEFT(r.date, 10) AS bill_date, r.total, r.return_status,
                    c.name AS party_name, TRIM(IFNULL(c.gst, '')) AS party_gstin, c.address AS party_address
             FROM retail r
             LEFT JOIN customer c ON c.id = r.customer_id
             WHERE LEFT(r.date, 10) >= ? AND LEFT(r.date, 10) <= ?
             ORDER BY r.date ASC, r.bill_id ASC",
            [$from, $to]
        );
    }

    private static function listPurchaseInRange(string $from, string $to): array
    {
        return DB::select(
            "SELECT 'purchase' AS doc_type, po.id, po.bill_id, LEFT(po.date, 10) AS bill_date, po.total, po.return_status,
                    v.name AS party_name, TRIM(IFNULL(v.gst, '')) AS party_gstin, v.address AS party_address
             FROM purchase_order po
             LEFT JOIN vendor v ON v.id = po.customer_id
             WHERE LEFT(po.date, 10) >= ? AND LEFT(po.date, 10) <= ?
             ORDER BY po.date ASC, po.bill_id ASC",
            [$from, $to]
        );
    }

    private static function getDeliveryDoc(int $id): ?array
    {
        $header = DB::table('order as o')
            ->leftJoin('customer as c', 'c.id', '=', 'o.customer_id')
            ->where('o.id', $id)
            ->selectRaw('o.*, c.name AS party_name, c.address AS party_address, c.mobile AS party_mobile, TRIM(IFNULL(c.gst, "")) AS party_gstin')
            ->first();

        if (!$header) {
            return null;
        }

        $lines = self::fetchLines('order_details', 'order_id', $id);

        return self::mapDoc('delivery', $header, $lines, 'Outward - Delivery');
    }

    private static function getRetailDoc(int $id): ?array
    {
        $header = DB::table('retail as r')
            ->leftJoin('customer as c', 'c.id', '=', 'r.customer_id')
            ->where('r.id', $id)
            ->selectRaw('r.*, c.name AS party_name, c.address AS party_address, c.mobile AS party_mobile, TRIM(IFNULL(c.gst, "")) AS party_gstin')
            ->first();

        if (!$header) {
            return null;
        }

        $lines = self::fetchLines('retail_details', 'order_id', $id);

        return self::mapDoc('retail', $header, $lines, 'Outward - Retail');
    }

    private static function getPurchaseDoc(int $id): ?array
    {
        $header = DB::table('purchase_order as po')
            ->leftJoin('vendor as v', 'v.id', '=', 'po.customer_id')
            ->where('po.id', $id)
            ->selectRaw('po.*, v.name AS party_name, v.address AS party_address, v.mobile AS party_mobile, TRIM(IFNULL(v.gst, "")) AS party_gstin')
            ->first();

        if (!$header) {
            return null;
        }

        $lines = self::fetchLines('purchase_order_details', 'order_id', $id);

        return self::mapDoc('purchase', $header, $lines, 'Inward - Purchase');
    }

    private static function fetchLines(string $table, string $fk, int $id): array
    {
        $rows = DB::select(
            "SELECT " . self::LINE_SELECT . "
             FROM {$table} d
             LEFT JOIN products p ON p.id = d.product_id
             LEFT JOIN hsn h ON h.id = p.hsn
             LEFT JOIN unit u ON u.id = p.unit
             WHERE d.{$fk} = ? AND d.status != 0
             ORDER BY d.id",
            [$id]
        );

        return array_map(function ($row) {
            return (array) $row;
        }, $rows);
    }

    private static function mapDoc(string $docType, $header, array $lines, string $supplyLabel): array
    {
        return [
            'doc_type' => $docType,
            'id' => (int) $header->id,
            'bill_id' => (string) $header->bill_id,
            'bill_date' => GstDateRange::sliceDate($header->date ?? ''),
            'total' => GstTaxCalculator::parseNum($header->total ?? 0),
            'return_status' => (int) ($header->return_status ?? 0),
            'party_name' => (string) ($header->party_name ?? ''),
            'party_gstin' => GstCompanyProfile::normGstin($header->party_gstin ?? ''),
            'party_address' => (string) ($header->party_address ?? ''),
            'party_mobile' => (string) ($header->party_mobile ?? ''),
            'supply_label' => $supplyLabel,
            'lines' => $lines,
        ];
    }

    public static function displayProductName(array $line): string
    {
        $name = trim((string) ($line['product_name'] ?? ''));
        if ($name !== '') {
            return $name;
        }
        $tamil = trim((string) ($line['product_tamilname'] ?? ''));
        if ($tamil !== '') {
            return $tamil;
        }

        return '#' . ($line['product_id'] ?? '');
    }
}
