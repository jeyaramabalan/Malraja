<?php

namespace App\Services\Gst;

use Illuminate\Http\Request;

class EInvoiceService
{
    public function preview(Request $request): array
    {
        $scope = strtolower((string) $request->query('scope', 'day'));
        $types = GstDateRange::parseDocTypes(
            $request->query('types'),
            config('gst.einvoice_doc_types')
        );

        if ($scope === 'bill') {
            $docType = strtolower((string) $request->query('docType', ''));
            if (!in_array($docType, config('gst.einvoice_doc_types'), true)) {
                throw new \InvalidArgumentException('docType required for scope=bill');
            }
            $id = (int) $request->query('id');
            if ($id <= 0 && $request->filled('billNo')) {
                $found = GstDocumentQuery::findDocIdByBillNo($docType, (string) $request->query('billNo'));
                if ($found) {
                    $id = $found;
                }
            }
            if ($id <= 0) {
                throw new \InvalidArgumentException('billNo or id required for scope=bill');
            }
            $doc = GstDocumentQuery::getDocument($docType, $id);
            if (!$doc) {
                return ['scope' => $scope, 'range' => null, 'items' => []];
            }
            $row = (object) [
                'doc_type' => $doc['doc_type'],
                'id' => $doc['id'],
                'bill_id' => $doc['bill_id'],
                'bill_date' => $doc['bill_date'],
                'total' => $doc['total'],
                'return_status' => $doc['return_status'],
                'party_name' => $doc['party_name'],
                'party_gstin' => $doc['party_gstin'],
                'party_address' => $doc['party_address'],
            ];

            return [
                'scope' => $scope,
                'range' => null,
                'items' => [GstEligibility::mapPreviewRow($row, [GstEligibility::class, 'assessEinvoice'])],
            ];
        }

        $range = GstDateRange::resolve($request->all());
        $rows = GstDocumentQuery::listHeadersInRange($range['from'], $range['to'], $types);
        $items = array_map(function ($row) {
            return GstEligibility::mapPreviewRow($row, [GstEligibility::class, 'assessEinvoice']);
        }, $rows);

        return ['scope' => $scope, 'range' => $range, 'items' => $items];
    }

    public function buildJson(string $docType, int $id): array
    {
        $doc = GstDocumentQuery::getDocument($docType, $id);
        if (!$doc) {
            throw new \RuntimeException('Bill not found', 404);
        }

        $company = GstCompanyProfile::get();
        $payload = NicEInvoiceBuilder::build($doc, $company, $company['state_code']);
        $safe = GstEligibility::safeFilename($doc['bill_id']);

        return [
            'payload' => $payload,
            'filename' => "einvoice-{$docType}-{$safe}.json",
        ];
    }

    public function buildPdf(string $docType, int $id): array
    {
        $doc = GstDocumentQuery::getDocument($docType, $id);
        if (!$doc) {
            throw new \RuntimeException('Bill not found', 404);
        }

        $company = GstCompanyProfile::get();
        $pdf = GstZipArchive::pdfFromView('reports.gst.e_invoice_pdf', [
            'doc' => $doc,
            'company' => $company,
            'company_state' => $company['state_code'],
        ]);

        $safe = GstEligibility::safeFilename($doc['bill_id']);

        return ['content' => $pdf, 'filename' => "einvoice-{$docType}-{$safe}.pdf"];
    }

    public function buildZip(Request $request): array
    {
        $scope = strtolower((string) $request->query('scope', 'day'));
        if ($scope === 'bill') {
            throw new \InvalidArgumentException('Use single-bill endpoints for scope=bill');
        }

        $range = GstDateRange::resolve($request->all());
        $types = GstDateRange::parseDocTypes(
            $request->query('types'),
            config('gst.einvoice_doc_types')
        );
        $selected = GstDateRange::parseSelected($request->query('selected'));

        if ($selected !== []) {
            $targets = $selected;
        } else {
            $rows = GstDocumentQuery::listHeadersInRange($range['from'], $range['to'], $types);
            $targets = [];
            foreach ($rows as $row) {
                if (GstEligibility::assessEinvoice($row)['eligible']) {
                    $targets[] = ['doc_type' => $row->doc_type, 'id' => (int) $row->id];
                }
            }
        }

        if ($targets === []) {
            throw new \RuntimeException('No eligible bills in this period', 404);
        }

        $entries = [];
        foreach ($targets as $target) {
            $json = $this->buildJson($target['doc_type'], $target['id']);
            $entries[] = [
                'name' => $json['filename'],
                'content' => json_encode($json['payload'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
            ];
            $pdf = $this->buildPdf($target['doc_type'], $target['id']);
            $entries[] = ['name' => $pdf['filename'], 'content' => $pdf['content']];
        }

        $label = $range['from'] === $range['to'] ? $range['from'] : ($range['from'] . '_to_' . $range['to']);

        return [
            'content' => GstZipArchive::create($entries),
            'filename' => 'e-invoices_' . $label . '.zip',
        ];
    }
}
