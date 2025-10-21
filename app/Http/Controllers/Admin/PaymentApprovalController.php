<?php

namespace App\Http\Controllers\Admin;

use App\Application\Payment\UseCases\GetAllPaymentsUseCase;
use App\Application\Payment\UseCases\GetPendingPaymentsUseCase;
use App\Application\Payment\UseCases\RejectPaymentUseCase;
use App\Application\Payment\UseCases\VerifyPaymentUseCase;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PaymentApprovalController extends Controller
{
    public function __construct(
        private readonly GetAllPaymentsUseCase $getAllPaymentsUseCase,
        private readonly GetPendingPaymentsUseCase $getPendingPaymentsUseCase,
        private readonly VerifyPaymentUseCase $verifyPaymentUseCase,
        private readonly RejectPaymentUseCase $rejectPaymentUseCase,
        private readonly \App\Application\Payment\UseCases\ResetPaymentUseCase $resetPaymentUseCase
    ) {}

    public function index(Request $request): Response
    {
        $status = $request->query('status');
        $payments = $this->getAllPaymentsUseCase->execute($status);

        $pendingCount = count($this->getPendingPaymentsUseCase->execute());

        return Inertia::render('Admin/Payments/Index', [
            'payments' => $payments,
            'pendingCount' => $pendingCount,
            'currentStatus' => $status,
        ]);
    }

    public function verify(Request $request, int $id)
    {
        $validated = $request->validate([
            'admin_notes' => 'nullable|string|max:1000',
        ]);

        try {
            $this->verifyPaymentUseCase->execute(
                $id,
                auth()->id(),
                $validated['admin_notes'] ?? null
            );

            return redirect()
                ->back()
                ->with('success', 'Payment verified successfully');
        } catch (\DomainException $e) {
            return redirect()
                ->back()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function reject(Request $request, int $id)
    {
        $validated = $request->validate([
            'reason' => 'required|string|max:1000',
        ]);

        try {
            $this->rejectPaymentUseCase->execute(
                $id,
                auth()->id(),
                $validated['reason']
            );

            return redirect()
                ->back()
                ->with('success', 'Payment rejected');
        } catch (\DomainException $e) {
            return redirect()
                ->back()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function reset(Request $request, int $id)
    {
        $validated = $request->validate([
            'reason' => 'required|string|max:1000',
        ]);

        try {
            $this->resetPaymentUseCase->execute(
                $id,
                auth()->id(),
                $validated['reason']
            );

            return redirect()
                ->back()
                ->with('success', 'Payment reset to pending status');
        } catch (\DomainException $e) {
            return redirect()
                ->back()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }
}
