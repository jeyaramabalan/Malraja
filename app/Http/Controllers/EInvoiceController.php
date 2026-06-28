<?php

namespace App\Http\Controllers;

use App\Services\Gst\EInvoiceService;
use Illuminate\Http\Request;

class EInvoiceController extends GstComplianceController
{
    private EInvoiceService $service;

    public function __construct(EInvoiceService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        $this->ensureAdminOrManager();

        return view('reports.e_invoice');
    }

    public function preview(Request $request)
    {
        $this->ensureAdminOrManager();

        return $this->runJson(fn () => $this->service->preview($request));
    }

    public function json(Request $request)
    {
        $this->ensureAdminOrManager();

        $docType = strtolower((string) $request->query('docType', ''));
        $id = (int) $request->query('id');
        if ($docType === '' || $id <= 0) {
            return response()->json(['error' => 'docType and id are required'], 400);
        }

        try {
            $result = $this->service->buildJson($docType, $id);

            return response(json_encode($result['payload'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), 200, [
                'Content-Type' => 'application/json; charset=utf-8',
                'Content-Disposition' => 'attachment; filename="' . $result['filename'] . '"',
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        } catch (\RuntimeException $e) {
            $code = $e->getCode() >= 400 && $e->getCode() < 600 ? (int) $e->getCode() : 500;

            return response()->json(['error' => $e->getMessage()], $code);
        }
    }

    public function pdf(Request $request)
    {
        $this->ensureAdminOrManager();

        $docType = strtolower((string) $request->query('docType', ''));
        $id = (int) $request->query('id');
        if ($docType === '' || $id <= 0) {
            return response()->json(['error' => 'docType and id are required'], 400);
        }

        return $this->runDownload(
            fn () => $this->service->buildPdf($docType, $id),
            'application/pdf'
        );
    }

    public function zip(Request $request)
    {
        $this->ensureAdminOrManager();

        return $this->runDownload(
            fn () => $this->service->buildZip($request),
            'application/zip'
        );
    }
}
