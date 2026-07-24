<?php

namespace App\Http\Controllers\BizBoost;

use App\Http\Controllers\Controller;
use App\Domain\BizBoost\Services\QrCodeService;
use App\Domain\BizBoost\Services\BusinessService;
use App\Domain\BizBoost\Repositories\QrCodeRepositoryInterface;
use Illuminate\Http\Request;
use Inertia\Inertia;

class QrCodeController extends Controller
{
    public function __construct(
        private QrCodeService $qrCodeService,
        private BusinessService $businessService,
        private QrCodeRepositoryInterface $qrCodeRepo,
    ) {}

    public function index(Request $request)
    {
        $business = $this->businessService->getBusinessOrFail($request->user()->id);

        return Inertia::render('BizBoost/QrCodes/Index', [
            'qrCodes' => $this->qrCodeRepo->findByBusiness($business->id),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:menu,website,menu_digital,whatsapp,promotion,social,review,order,contact,payment,other',
            'data' => 'nullable|string|max:500',
            'design' => 'nullable|array',
        ]);

        $business = $this->businessService->getBusinessOrFail($request->user()->id);
        $qrCode = $this->qrCodeService->generate($business->id, $validated);

        return back()->with('success', 'QR Code "' . $qrCode->name . '" generated successfully.');
    }

    public function update(Request $request, int $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:menu,website,menu_digital,whatsapp,promotion,social,review,order,contact,payment,other',
            'data' => 'nullable|string|max:500',
            'design' => 'nullable|array',
            'is_active' => 'boolean',
        ]);

        $business = $this->businessService->getBusinessOrFail($request->user()->id);
        $this->qrCodeService->update($business->id, $id, $validated);

        return back()->with('success', 'QR Code updated successfully.');
    }

    public function destroy(Request $request, int $id)
    {
        $this->qrCodeService->delete($id);
        return back()->with('success', 'QR Code deleted successfully.');
    }

    public function download(Request $request, int $id)
    {
        $business = $this->businessService->getBusinessOrFail($request->user()->id);
        $qrCode = $this->qrCodeRepo->findById($business->id, $id);

        if (!$qrCode) {
            abort(404);
        }

        return response()->streamDownload(function () use ($qrCode) {
            echo $this->qrCodeService->getSvgContent($qrCode);
        }, $qrCode->name . '.svg', ['Content-Type' => 'image/svg+xml']);
    }

    public function print(Request $request, int $id)
    {
        $business = $this->businessService->getBusinessOrFail($request->user()->id);
        $qrCode = $this->qrCodeRepo->findById($business->id, $id);

        if (!$qrCode) {
            abort(404);
        }

        return Inertia::render('BizBoost/QrCodes/Print', [
            'qrCode' => $qrCode->toArray(),
            'business' => $business->toArray(),
        ]);
    }
}