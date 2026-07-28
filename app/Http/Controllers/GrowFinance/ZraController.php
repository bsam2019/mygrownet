<?php

namespace App\Http\Controllers\GrowFinance;

use App\Domain\GrowFinance\Services\ZraEInvoiceService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ZraController extends Controller
{
    public function __construct(
        private ZraEInvoiceService $zraService,
    ) {}

    public function submitInvoice(Request $request, int $invoiceId): JsonResponse
    {
        $result = $this->zraService->submitInvoice($invoiceId);
        return response()->json($result);
    }

    public function verifyInvoice(Request $request): JsonResponse
    {
        $reference = $request->input('reference');
        if (empty($reference)) {
            return response()->json(['error' => 'Reference required'], 422);
        }
        $result = $this->zraService->verifyInvoice($reference);
        return response()->json($result);
    }

    public function health(): JsonResponse
    {
        return response()->json($this->zraService->healthCheck());
    }
}
