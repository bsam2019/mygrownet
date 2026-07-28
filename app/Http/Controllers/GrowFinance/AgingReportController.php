<?php

namespace App\Http\Controllers\GrowFinance;

use App\Domain\GrowFinance\Services\AgingReportService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AgingReportController extends Controller
{
    public function __construct(
        private AgingReportService $agingService,
    ) {}

    public function arAging(Request $request): Response
    {
        $data = $this->agingService->getArAging($request->user()->id);

        return Inertia::render('GrowFinance/Reports/ArAging', $data);
    }

    public function apAging(Request $request): Response
    {
        $data = $this->agingService->getApAging($request->user()->id);

        return Inertia::render('GrowFinance/Reports/ApAging', $data);
    }

    public function customerDetail(Request $request, int $customerId): Response
    {
        $data = $this->agingService->getCustomerAgingDetail($request->user()->id, $customerId);

        return Inertia::render('GrowFinance/Customers/AgingDetail', $data);
    }

    public function vendorDetail(Request $request, int $vendorId): Response
    {
        $data = $this->agingService->getVendorAgingDetail($request->user()->id, $vendorId);

        return Inertia::render('GrowFinance/Vendors/AgingDetail', $data);
    }
}
