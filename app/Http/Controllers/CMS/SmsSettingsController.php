<?php

namespace App\Http\Controllers\CMS;

use App\Http\Controllers\Controller;
use App\Infrastructure\Persistence\Eloquent\CMS\CompanyModel;
use App\Services\CMS\SmsService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SmsSettingsController extends Controller
{
    public function __construct(
        private SmsService $smsService
    ) {}

    /**
     * Show SMS settings page
     */
    public function index(Request $request)
    {
        $companyId = $request->user()->cmsUser->company_id;
        $company = CompanyModel::find($companyId);

        $smsSettings = $company->sms_settings ?? [
            'enabled' => false,
            'provider' => null,
            'api_key' => null,
            'username' => null,
            'sender_id' => null,
            'account_sid' => null,
            'auth_token' => null,
            'from_number' => null,
        ];

        $statistics = $this->smsService->getStatistics($companyId);

        return Inertia::render('CMS/Settings/Sms', [
            'settings' => $smsSettings,
            'statistics' => $statistics,
            'isEnabled' => $this->smsService->isEnabled($companyId),
        ]);
    }

    /**
     * Update SMS settings
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'enabled' => 'required|boolean',
            'provider' => 'nullable|in:africas_talking,twilio',
            'api_key' => 'nullable|string',
            'username' => 'nullable|string',
            'sender_id' => 'nullable|string|max:11',
            'account_sid' => 'nullable|string',
            'auth_token' => 'nullable|string',
            'from_number' => 'nullable|string',
        ]);

        $companyId = $request->user()->cmsUser->company_id;
        $company = CompanyModel::find($companyId);

        if (!$company) {
            return response()->json([
                'success' => false,
                'error' => 'Company not found',
            ], 404);
        }

        $company->update([
            'sms_settings' => $validated,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'SMS settings updated successfully',
        ]);
    }

    /**
     * Test SMS connection
     */
    public function testConnection(Request $request)
    {
        $validated = $request->validate([
            'test_number' => 'required|string',
        ]);

        $companyId = $request->user()->cmsUser->company_id;

        $result = $this->smsService->testConnection($companyId, $validated['test_number']);

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'message' => 'Test SMS sent successfully!',
            ]);
        }

        return response()->json([
            'success' => false,
            'error' => $result['error'] ?? 'Failed to send test SMS',
        ], 400);
    }

    /**
     * Get SMS logs
     */
    public function logs(Request $request)
    {
        $companyId = $request->user()->cmsUser->company_id;
        $logs = $this->smsService->getLogs($companyId);

        return Inertia::render('CMS/Settings/SmsLogs', [
            'logs' => $logs,
        ]);
    }
}
