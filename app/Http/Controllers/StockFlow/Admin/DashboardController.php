<?php

namespace App\Http\Controllers\StockFlow\Admin;

use App\Http\Controllers\Controller;
use App\Domain\StockFlow\Repositories\CompanyRepositoryInterface;
use App\Domain\StockFlow\Repositories\UserRepositoryInterface;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function __construct(
        private CompanyRepositoryInterface $companyRepository,
        private UserRepositoryInterface $userRepository,
    ) {}

    public function index()
    {
        $companies = $this->companyRepository->findAll();
        $users = $this->userRepository->findAll();

        return Inertia::render('StockFlow/Admin/Dashboard', [
            'stats' => [
                'total_companies' => count($companies),
                'active_companies' => count(array_filter($companies, fn($c) => $c->getStatus() === 'active')),
                'total_users' => count($users),
            ],
        ]);
    }
}
