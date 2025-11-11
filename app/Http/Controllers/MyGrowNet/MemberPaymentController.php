<?php

namespace App\Http\Controllers\MyGrowNet;

use App\Application\Payment\DTOs\SubmitPaymentDTO;
use App\Application\Payment\UseCases\GetUserPaymentsUseCase;
use App\Application\Payment\UseCases\SubmitPaymentUseCase;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MemberPaymentController extends Controller
{
    public function __construct(
        private readonly GetUserPaymentsUseCase $getUserPaymentsUseCase,
        private readonly SubmitPaymentUseCase $submitPaymentUseCase
    ) {}

    public function index(): Response
    {
        $payments = $this->getUserPaymentsUseCase->execute(auth()->id());

        // Convert domain entities to arrays for Inertia
        $paymentsData = array_map(function ($payment) {
            return [
                'id' => $payment->id(),
                'amount' => $payment->amount()->value(),
                'payment_method' => $payment->paymentMethod()->value,
                'payment_reference' => $payment->paymentReference(),
                'phone_number' => $payment->phoneNumber(),
                'payment_type' => $payment->paymentType()->value,
                'notes' => $payment->notes(),
                'status' => $payment->status()->value,
                'admin_notes' => $payment->adminNotes(),
                'verified_at' => $payment->verifiedAt()?->format('Y-m-d H:i:s'),
                'created_at' => $payment->createdAt()->format('Y-m-d H:i:s'),
            ];
        }, $payments);

        return Inertia::render('MyGrowNet/PaymentHistory', [
            'payments' => $paymentsData,
        ]);
    }

    public function create(Request $request): Response
    {
        // Get payment context from session (e.g., from starter kit purchase)
        $paymentContext = session('payment_context');
        
        return Inertia::render('MyGrowNet/SubmitPayment', [
            'userPhone' => auth()->user()->phone ?? '',
            'paymentContext' => $paymentContext,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:50',
            'payment_method' => 'required|in:mtn_momo,airtel_money,bank_transfer,cash,other',
            'payment_reference' => 'required|string|max:255',
            'phone_number' => 'required|string|max:20',
            'payment_type' => 'required|in:wallet_topup,subscription,workshop,product,learning_pack,coaching,upgrade,other',
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            $dto = SubmitPaymentDTO::fromArray([
                'user_id' => auth()->id(),
                ...$validated
            ]);

            $this->submitPaymentUseCase->execute($dto);

            // Check if request is from mobile
            if ($request->input('_mobile') || $request->header('X-Mobile-Request')) {
                return back()->with('success', 'Payment submitted successfully! We will verify it shortly.');
            }

            return redirect()
                ->route('mygrownet.payments.index')
                ->with('success', 'Payment submitted successfully! We will verify it shortly.');
        } catch (\DomainException $e) {
            return back()
                ->withErrors(['payment_reference' => $e->getMessage()])
                ->withInput();
        }
    }
}
