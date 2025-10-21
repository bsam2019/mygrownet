<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Application\ProfitSharing\UseCases\CreateQuarterlyProfitShareUseCase;
use App\Application\ProfitSharing\UseCases\ApproveProfitShareUseCase;
use App\Application\ProfitSharing\UseCases\DistributeProfitShareUseCase;
use App\Application\ProfitSharing\DTOs\CreateProfitShareDTO;
use App\Domain\ProfitSharing\Repositories\QuarterlyProfitShareRepository;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProfitSharingController extends Controller
{
    public function __construct(
        private QuarterlyProfitShareRepository $repository,
        private CreateQuarterlyProfitShareUseCase $createUseCase,
        private ApproveProfitShareUseCase $approveUseCase,
        private DistributeProfitShareUseCase $distributeUseCase
    ) {}

    public function index()
    {
        $profitShares = $this->repository->findAll();

        return Inertia::render('Admin/ProfitSharing/Index', [
            'profitShares' => array_map(function ($share) {
                return [
                    'id' => $share->id(),
                    'quarter' => $share->quarter()->label(),
                    'year' => $share->quarter()->year(),
                    'quarter_number' => $share->quarter()->quarter(),
                    'total_project_profit' => $share->totalProjectProfit()->formatted(),
                    'member_share_amount' => $share->memberShareAmount()->formatted(),
                    'company_retained' => $share->companyRetained()->formatted(),
                    'total_active_members' => $share->totalActiveMembers(),
                    'distribution_method' => $share->distributionMethod(),
                    'status' => $share->status(),
                    'approved_at' => $share->approvedAt()?->format('Y-m-d H:i:s'),
                    'distributed_at' => $share->distributedAt()?->format('Y-m-d H:i:s'),
                ];
            }, $profitShares),
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/ProfitSharing/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'year' => 'required|integer|min:2020|max:2100',
            'quarter' => 'required|integer|min:1|max:4',
            'total_project_profit' => 'required|numeric|min:0',
            'distribution_method' => 'required|in:bp_based,level_based',
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            $dto = new CreateProfitShareDTO(
                year: $validated['year'],
                quarter: $validated['quarter'],
                totalProjectProfit: $validated['total_project_profit'],
                distributionMethod: $validated['distribution_method'],
                notes: $validated['notes'] ?? null,
                createdBy: auth()->id()
            );

            $this->createUseCase->execute($dto);

            return redirect()->route('admin.profit-sharing.index')
                ->with('success', 'Quarterly profit share created and calculated successfully');
        } catch (\DomainException $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }


    public function approve(int $id)
    {
        try {
            $this->approveUseCase->execute($id, auth()->id());

            return back()->with('success', 'Profit share approved successfully');
        } catch (\DomainException $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function distribute(int $id)
    {
        try {
            $this->distributeUseCase->execute($id);

            return back()->with('success', 'Profit share distributed to member wallets successfully');
        } catch (\DomainException $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
