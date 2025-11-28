<?php

namespace App\Http\Controllers\Investor\Concerns;

use App\Domain\Investor\Repositories\InvestorAccountRepositoryInterface;

trait RequiresInvestorAuth
{
    protected function getAuthenticatedInvestor()
    {
        $investorId = session('investor_id');
        
        if (!$investorId) {
            return null;
        }

        $repository = app(InvestorAccountRepositoryInterface::class);
        return $repository->findById($investorId);
    }

    protected function requireInvestorAuth()
    {
        $investor = $this->getAuthenticatedInvestor();
        
        if (!$investor) {
            return redirect()->route('investor.login');
        }

        return $investor;
    }
}
