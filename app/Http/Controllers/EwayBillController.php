<?php

namespace App\Http\Controllers;

use App\Services\Gst\EwayBillService;
use App\Services\Gst\GstEligibility;
use Illuminate\Http\Request;

class EwayBillController extends GstComplianceController
{
    private EwayBillService $service;

    public function __construct(EwayBillService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        $this->ensureAdminOrManager();

        return view('reports.eway_bill');
    }

    public function preview(Request $request)
    {
        $this->ensureAdminOrManager();

        return $this->runJson(fn () => $this->service->preview($request));
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
            fn () => $this->service->buildPdf($docType, $id, GstEligibility::parseTransport($request->all())),
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
