<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Infrastructure\Persistence\Eloquent\BMS\PaymentModel;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PortalPaymentController extends Controller
{
    public function index(Request $request): Response
    {
        $customerIds = $request->user()->portalCustomers()->pluck('customer_id');

        $payments = PaymentModel::whereIn('customer_id', $customerIds)
            ->with(['invoice', 'receivedBy'])
            ->latest()
            ->paginate(20);

        return Inertia::render('Portal/Payments', ['payments' => $payments]);
    }
}
