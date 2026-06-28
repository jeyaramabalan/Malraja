<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpKernel\Exception\HttpException;

abstract class GstComplianceController extends Controller
{
    protected function ensureAdminOrManager(): void
    {
        $user = Auth::user();
        if (!$user || !in_array((int) $user->role, [1, 2], true)) {
            abort(403, 'Access denied');
        }
    }

    protected function runJson(callable $fn)
    {
        try {
            return response()->json($fn());
        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        } catch (\RuntimeException $e) {
            $code = $e->getCode() >= 400 && $e->getCode() < 600 ? (int) $e->getCode() : 500;

            return response()->json(['error' => $e->getMessage()], $code);
        }
    }

    protected function runDownload(callable $fn, string $contentType)
    {
        try {
            $result = $fn();

            return response($result['content'], 200, [
                'Content-Type' => $contentType,
                'Content-Disposition' => 'attachment; filename="' . $result['filename'] . '"',
            ]);
        } catch (\InvalidArgumentException $e) {
            throw new HttpException(400, $e->getMessage());
        } catch (\RuntimeException $e) {
            $code = $e->getCode() >= 400 && $e->getCode() < 600 ? (int) $e->getCode() : 500;
            throw new HttpException($code, $e->getMessage());
        }
    }
}
