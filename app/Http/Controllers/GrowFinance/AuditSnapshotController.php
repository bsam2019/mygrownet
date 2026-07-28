<?php

namespace App\Http\Controllers\GrowFinance;

use App\Domain\GrowFinance\Services\ReportSnapshotService;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AuditSnapshotController extends Controller
{
    public function __construct(
        private ReportSnapshotService $snapshotService,
    ) {}

    public function index(Request $request): Response
    {
        $businessId = $request->user()->id;
        $type = $request->input('type', 'trial_balance');

        return Inertia::render('GrowFinance/Reports/AuditSnapshots', [
            'snapshots' => $this->snapshotService->getSnapshotsByType($businessId, $type),
            'currentType' => $type,
        ]);
    }

    public function take(Request $request): RedirectResponse
    {
        $businessId = $request->user()->id;
        $type = $request->input('type', 'trial_balance');
        $asOf = $request->input('as_of', date('Y-m-d'));

        $this->snapshotService->takeSnapshotWithHash($businessId, $type, new \DateTimeImmutable($asOf));
        return redirect()->back()->with('success', ucwords(str_replace('_', ' ', $type)) . ' snapshot taken');
    }

    public function verify(Request $request, int $id)
    {
        $result = $this->snapshotService->verifySnapshot($id);
        return response()->json($result);
    }

    public function lock(Request $request, int $id): RedirectResponse
    {
        try {
            $this->snapshotService->lockSnapshot($id);
            return redirect()->back()->with('success', 'Snapshot locked');
        } catch (\DomainException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
