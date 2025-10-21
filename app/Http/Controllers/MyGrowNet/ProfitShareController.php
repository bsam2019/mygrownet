<?php

namespace App\Http\Controllers\MyGrowNet;

use App\Http\Controllers\Controller;
use App\Domain\ProfitSharing\Repositories\MemberProfitShareRepository;
use Inertia\Inertia;

class ProfitShareController extends Controller
{
    public function __construct(
        private MemberProfitShareRepository $repository
    ) {}

    public function index()
    {
        $memberShares = $this->repository->findByUserId(auth()->id());

        return Inertia::render('MyGrowNet/ProfitShares', [
            'profitShares' => array_map(function ($share) {
                return [
                    'id' => $share->id(),
                    'professional_level' => $share->professionalLevel(),
                    'share_amount' => number_format($share->shareAmount(), 2),
                    'status' => $share->status(),
                    'paid_at' => $share->paidAt()?->format('Y-m-d H:i:s'),
                    'created_at' => $share->createdAt()->format('Y-m-d'),
                ];
            }, $memberShares),
        ]);
    }
}
