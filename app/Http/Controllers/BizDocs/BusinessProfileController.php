<?php

namespace App\Http\Controllers\BizDocs;

use App\Domain\BizDocs\BusinessIdentity\Entities\BusinessProfile;
use App\Domain\BizDocs\BusinessIdentity\Repositories\BusinessProfileRepositoryInterface;
use App\Application\BizDocs\Services\FileStorageService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BusinessProfileController extends Controller
{
    public function __construct(
        private readonly BusinessProfileRepositoryInterface $businessProfileRepository,
        private readonly FileStorageService $fileStorageService
    ) {
    }

    public function dashboard(Request $request)
    {
        $user = $request->user();
        $businessProfile = $this->businessProfileRepository->findByUserId($user->id);

        $profileData = null;
        if ($businessProfile) {
            $profileData = $businessProfile->toArray();
            // Add full URLs for images
            if ($businessProfile->logo()) {
                $profileData['logoUrl'] = $this->fileStorageService->getUrl($businessProfile->logo());
            }
            if ($businessProfile->signatureImage()) {
                $profileData['signatureUrl'] = $this->fileStorageService->getUrl($businessProfile->signatureImage());
            }
        }

        return Inertia::render('BizDocs/Dashboard', [
            'businessProfile' => $profileData,
        ]);
    }

    public function setup(Request $request)
    {
        $user = $request->user();
        $businessProfile = $this->businessProfileRepository->findByUserId($user->id);

        $profileData = null;
        if ($businessProfile) {
            $profileData = $businessProfile->toArray();
            // Add full URLs for images
            if ($businessProfile->logo()) {
                $profileData['logoUrl'] = $this->fileStorageService->getUrl($businessProfile->logo());
            }
            if ($businessProfile->signatureImage()) {
                $profileData['signatureUrl'] = $this->fileStorageService->getUrl($businessProfile->signatureImage());
            }
        }

        return Inertia::render('BizDocs/BusinessProfile/Setup', [
            'businessProfile' => $profileData,
        ]);
    }

    public function store(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'business_name' => 'required|string|max:255',
            'address' => 'required|string',
            'phone' => 'required|string|max:50',
            'email' => 'nullable|email|max:255',
            'tpin' => 'nullable|string|max:50',
            'website' => 'nullable|string|max:255',
            'bank_name' => 'nullable|string|max:255',
            'bank_account' => 'nullable|string|max:100',
            'bank_branch' => 'nullable|string|max:255',
            'default_currency' => 'nullable|string|size:3',
            'default_tax_rate' => 'nullable|numeric|min:0|max:100',
            'default_terms' => 'nullable|string',
            'default_notes' => 'nullable|string',
            'default_payment_instructions' => 'nullable|string',
            'prepared_by' => 'nullable|string|max:255',
            'logo' => 'nullable|image|max:2048', // 2MB max
            'signature' => 'nullable|image|max:1024', // 1MB max
        ]);

        // Auto-prepend https:// to website if no protocol specified
        if (!empty($validated['website'])) {
            $website = $validated['website'];
            if (!preg_match('/^https?:\/\//i', $website)) {
                $validated['website'] = 'https://' . $website;
            }
        }

        try {
            // Check if profile already exists
            $existingProfile = $this->businessProfileRepository->findByUserId($user->id);

            // Handle logo upload
            $logoPath = $existingProfile?->logo();
            if ($request->hasFile('logo')) {
                // Delete old logo if exists
                if ($logoPath) {
                    $this->fileStorageService->deleteFile($logoPath);
                }
                $logoPath = $this->fileStorageService->uploadBusinessFile(
                    $request->file('logo'),
                    $user->id,
                    'logo'
                );
            }

            // Handle signature upload
            $signaturePath = $existingProfile?->signatureImage();
            if ($request->hasFile('signature')) {
                // Delete old signature if exists
                if ($signaturePath) {
                    $this->fileStorageService->deleteFile($signaturePath);
                }
                $signaturePath = $this->fileStorageService->uploadBusinessFile(
                    $request->file('signature'),
                    $user->id,
                    'signature'
                );
            }

            if ($existingProfile) {
                // Update existing profile
                $businessProfile = BusinessProfile::fromPersistence(
                    $existingProfile->id(),
                    $user->id,
                    $validated['business_name'],
                    $validated['address'],
                    $validated['phone'],
                    $validated['email'] ?? null,
                    $logoPath,
                    $validated['tpin'] ?? null,
                    $validated['website'] ?? null,
                    $validated['bank_name'] ?? null,
                    $validated['bank_account'] ?? null,
                    $validated['bank_branch'] ?? null,
                    $validated['default_currency'] ?? 'ZMW',
                    $validated['default_tax_rate'] ?? 16.00,
                    $validated['default_terms'] ?? null,
                    $validated['default_notes'] ?? null,
                    $validated['default_payment_instructions'] ?? null,
                    $signaturePath,
                    $existingProfile->stampImage(),
                    $validated['prepared_by'] ?? null
                );
            } else {
                // Create new profile with all fields
                $businessProfile = BusinessProfile::fromPersistence(
                    null,
                    $user->id,
                    $validated['business_name'],
                    $validated['address'],
                    $validated['phone'],
                    $validated['email'] ?? null,
                    $logoPath,
                    $validated['tpin'] ?? null,
                    $validated['website'] ?? null,
                    $validated['bank_name'] ?? null,
                    $validated['bank_account'] ?? null,
                    $validated['bank_branch'] ?? null,
                    $validated['default_currency'] ?? 'ZMW',
                    $validated['default_tax_rate'] ?? 16.00,
                    $validated['default_terms'] ?? null,
                    $validated['default_notes'] ?? null,
                    $validated['default_payment_instructions'] ?? null,
                    $signaturePath,
                    null,  // stampImage
                    $validated['prepared_by'] ?? null
                );
            }

            $savedProfile = $this->businessProfileRepository->save($businessProfile);
            
            // Clear template preview cache when business profile is updated
            // This ensures previews reflect the new logo/signature
            \Cache::forget("template_preview_*_{$savedProfile->id()}");
            
            // Clear all template previews for this business (wildcard pattern)
            $templates = \App\Infrastructure\BizDocs\Persistence\Eloquent\DocumentTemplateModel::all();
            foreach ($templates as $template) {
                \Cache::forget("template_preview_{$template->id}_{$savedProfile->id()}");
            }

            $message = $existingProfile ? 'Business profile updated successfully' : 'Business profile created successfully';

            return redirect()
                ->route('bizdocs.dashboard')
                ->with('success', $message);
        } catch (\Exception $e) {
            \Log::error('Business profile save failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()
                ->withInput()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function update(Request $request)
    {
        return $this->store($request);
    }

    public function edit(Request $request)
    {
        $user = $request->user();
        $businessProfile = $this->businessProfileRepository->findByUserId($user->id);

        if (!$businessProfile) {
            return redirect()->route('bizdocs.setup');
        }

        $profileData = $businessProfile->toArray();
        // Add full URLs for images
        if ($businessProfile->logo()) {
            $profileData['logoUrl'] = $this->fileStorageService->getUrl($businessProfile->logo());
        }
        if ($businessProfile->signatureImage()) {
            $profileData['signatureUrl'] = $this->fileStorageService->getUrl($businessProfile->signatureImage());
        }

        return Inertia::render('BizDocs/BusinessProfile/Edit', [
            'businessProfile' => $profileData,
        ]);
    }
}
