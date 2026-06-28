<?php

namespace App\Http\Controllers;

use App\Http\Requests\IdVerificationRequest;
use App\Models\IdVerification;
use App\Services\IdVerificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class IdVerificationController extends Controller
{
    public function __construct(
        private IdVerificationService $idVerificationService
    ) {}

    /**
     * Show ID verification form
     */
    public function show()
    {
        $user = Auth::user();
        $currentVerification = $user->idVerifications()
            ->latest()
            ->first();

        return Inertia::render('IdVerification/Show', [
            'currentVerification' => $currentVerification,
            'documentTypes' => IdVerification::getDocumentTypes(),
            'requiresVerification' => $this->idVerificationService->requiresVerification($user),
        ]);
    }

    /**
     * Submit ID verification
     */
    public function store(IdVerificationRequest $request)
    {
        $user = Auth::user();

        // Check for duplicate document number
        if ($this->idVerificationService->checkDuplicateDocument(
            $request->document_type,
            $request->document_number,
            $user->id
        )) {
            return back()->withErrors([
                'document_number' => 'This document number is already registered with another account.'
            ]);
        }

        // Validate document number format
        if (!$this->idVerificationService->validateDocumentNumber(
            $request->document_type,
            $request->document_number
        )) {
            return back()->withErrors([
                'document_number' => 'Invalid document number format for the selected document type.'
            ]);
        }

        $verification = $this->idVerificationService->submitVerification(
            $user,
            $request->document_type,
            $request->document_number,
            $request->file('document_front'),
            $request->file('document_back'),
            $request->file('selfie')
        );

        return redirect()->route('verification.show')
            ->with('success', 'ID verification documents submitted successfully. Review typically takes 1-2 business days.');
    }

    /**
     * Admin: List pending verifications
     */
    public function adminIndex()
    {
        $this->authorize('manage-users');

        $pendingVerifications = $this->idVerificationService->getPendingVerifications();

        return Inertia::render('Admin/IdVerifications/Index', [
            'verifications' => $pendingVerifications,
        ]);
    }

    /**
     * Admin: Show verification details
     */
    public function adminShow(IdVerification $verification)
    {
        $this->authorize('manage-users');

        $verification->load(['user', 'reviewedBy']);

        return Inertia::render('Admin/IdVerifications/Show', [
            'verification' => $verification,
        ]);
    }

    /**
     * Admin: Approve verification
     */
    public function approve(IdVerification $verification)
    {
        $this->authorize('manage-users');

        $this->idVerificationService->approveVerification($verification, Auth::id());

        return back()->with('success', 'ID verification approved successfully.');
    }

    /**
     * Admin: Reject verification
     */
    public function reject(IdVerification $verification, Request $request)
    {
        $this->authorize('manage-users');

        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $this->idVerificationService->rejectVerification(
            $verification,
            $request->reason,
            Auth::id()
        );

        return back()->with('success', 'ID verification rejected.');
    }

    /**
     * Download verification document (admin only)
     */
    public function downloadDocument(IdVerification $verification, string $type)
    {
        $this->authorize('manage-users');

        $path = match ($type) {
            'front' => $verification->document_front_path,
            'back' => $verification->document_back_path,
            'selfie' => $verification->selfie_path,
            default => null,
        };

        if (!$path || !Storage::disk('private')->exists($path)) {
            abort(404);
        }

        return Storage::disk('private')->download($path);
    }
}