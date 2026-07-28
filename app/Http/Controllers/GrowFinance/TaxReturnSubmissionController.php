<?php

namespace App\Http\Controllers\GrowFinance;

use App\Domain\GrowFinance\Services\ZraTaxReturnService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TaxReturnSubmissionController extends Controller
{
    public function __construct(
        private ZraTaxReturnService $submissionService,
    ) {}

    public function index(Request $request)
    {
        $businessId = $request->user()->id;

        $filingCalendar = [];
        try {
            $filingCalendar = $this->submissionService->getFilingCalendar();
        } catch (\Throwable $e) {
            // Calendar fetch is non-critical
        }

        return Inertia::render('GrowFinance/Reports/TaxReturnSubmission', [
            'filingCalendar' => $filingCalendar,
        ]);
    }

    public function submit(Request $request): JsonResponse
    {
        $businessId = $request->user()->id;
        $period = $request->input('period', date('Y-m'));

        $result = $this->submissionService->submitVatReturn($businessId, $period);
        return response()->json($result);
    }

    public function status(Request $request): JsonResponse
    {
        $reference = $request->input('reference');
        if (empty($reference)) {
            return response()->json(['error' => 'Reference required'], 422);
        }

        return response()->json($this->submissionService->checkSubmissionStatus($reference));
    }
}
