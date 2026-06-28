<?php

namespace App\Http\Controllers\GrowBiz;

use App\Http\Controllers\Controller;
use App\Domain\GrowBiz\Services\EmployeeInvitationService;
use App\Domain\GrowBiz\Exceptions\OperationFailedException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class InvitationController extends Controller
{
    public function __construct(
        private EmployeeInvitationService $invitationService
    ) {}

    /**
     * Show invitation acceptance page (via email link)
     */
    public function showAcceptPage(string $token)
    {
        $data = $this->invitationService->getInvitationByToken($token);

        if (!$data) {
            return Inertia::render('GrowBiz/Invitation/Invalid', [
                'message' => 'This invitation link is invalid or has expired.',
            ]);
        }

        if (!$data['is_valid']) {
            return Inertia::render('GrowBiz/Invitation/Invalid', [
                'message' => 'This invitation has expired or already been used.',
            ]);
        }

        return Inertia::render('GrowBiz/Invitation/Accept', [
            'invitation' => $data['invitation'],
            'employeeName' => $data['employee_name'],
            'businessName' => $data['business_name'],
            'position' => $data['position'],
            'token' => $token,
            'isLoggedIn' => Auth::check(),
        ]);
    }

    /**
     * Accept invitation via email token (authenticated user)
     */
    public function acceptByToken(Request $request, string $token)
    {
        if (!Auth::check()) {
            // Store token in session and redirect to login
            session(['pending_invitation_token' => $token]);
            return redirect()->route('login')
                ->with('info', 'Please log in or register to accept the invitation.');
        }

        try {
            $employee = $this->invitationService->acceptByToken($token, Auth::id());

            return redirect()->route('growbiz.dashboard')
                ->with('success', 'Welcome! You have successfully joined the team.');
        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage());
        } catch (OperationFailedException $e) {
            return back()->with('error', 'Failed to accept invitation. Please try again.');
        }
    }

    /**
     * Accept invitation via code
     */
    public function acceptByCode(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|size:6',
        ]);

        $code = strtoupper($validated['code']);

        // If user is not logged in, store code and redirect to login
        if (!Auth::check()) {
            session(['pending_invitation_code' => $code]);
            return redirect()->route('login')
                ->with('info', 'Please log in or create an account to accept the invitation.');
        }

        try {
            $employee = $this->invitationService->acceptByCode($code, Auth::id());

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'You have successfully joined the team!',
                    'employee' => $employee->toArray(),
                ]);
            }

            return redirect()->route('growbiz.dashboard')
                ->with('success', 'Welcome! You have successfully joined the team.');
        } catch (\DomainException $e) {
            \Log::warning('Invitation code error', ['code' => $code, 'error' => $e->getMessage()]);
            if ($request->wantsJson()) {
                return response()->json(['error' => $e->getMessage()], 400);
            }
            return back()->with('error', $e->getMessage())->withInput();
        } catch (OperationFailedException $e) {
            \Log::error('Invitation operation failed', ['code' => $code, 'error' => $e->getMessage()]);
            if ($request->wantsJson()) {
                return response()->json(['error' => 'Failed to accept invitation'], 500);
            }
            return back()->with('error', 'Failed to accept invitation. Please try again.')->withInput();
        }
    }

    /**
     * Show page to enter invitation code
     */
    public function showCodeEntry()
    {
        return Inertia::render('GrowBiz/Invitation/EnterCode', [
            'isLoggedIn' => Auth::check(),
        ]);
    }

    /**
     * Handle post-login invitation acceptance (token or code)
     */
    public function handlePendingInvitation()
    {
        if (!Auth::check()) {
            return redirect()->route('home');
        }

        // Check for pending token first
        $token = session('pending_invitation_token');
        if ($token) {
            session()->forget('pending_invitation_token');
            try {
                $employee = $this->invitationService->acceptByToken($token, Auth::id());
                return redirect()->route('growbiz.dashboard')
                    ->with('success', 'Welcome! You have successfully joined the team.');
            } catch (\Exception $e) {
                return redirect()->route('home')
                    ->with('error', 'Failed to process invitation. It may have expired.');
            }
        }

        // Check for pending code
        $code = session('pending_invitation_code');
        if ($code) {
            session()->forget('pending_invitation_code');
            try {
                $employee = $this->invitationService->acceptByCode($code, Auth::id());
                return redirect()->route('growbiz.dashboard')
                    ->with('success', 'Welcome! You have successfully joined the team.');
            } catch (\Exception $e) {
                return redirect()->route('growbiz.invitation.code')
                    ->with('error', 'Failed to process invitation. ' . $e->getMessage());
            }
        }

        return redirect()->route('home');
    }
}
