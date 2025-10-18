<?php

namespace Tests\Unit\Models;

use App\Models\WithdrawalRequest;
use App\Models\User;
use App\Models\Investment;
use App\Models\InvestmentTier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class WithdrawalRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_belongs_to_user(): void
    {
        $user = User::factory()->create();
        $withdrawalRequest = WithdrawalRequest::factory()->create(['user_id' => $user->id]);
        
        $this->assertInstanceOf(User::class, $withdrawalRequest->user);
        $this->assertEquals($user->id, $withdrawalRequest->user->id);
    }

    public function test_belongs_to_investment(): void
    {
        $investment = Investment::factory()->create();
        $withdrawalRequest = WithdrawalRequest::factory()->create(['investment_id' => $investment->id]);
        
        $this->assertInstanceOf(Investment::class, $withdrawalRequest->investment);
        $this->assertEquals($investment->id, $withdrawalRequest->investment->id);
    }

    public function test_pending_scope(): void
    {
        WithdrawalRequest::factory()->create(['status' => 'pending']);
        WithdrawalRequest::factory()->create(['status' => 'approved']);
        WithdrawalRequest::factory()->create(['status' => 'pending']);
        
        $pendingRequests = WithdrawalRequest::pending()->get();
        
        $this->assertCount(2, $pendingRequests);
        $pendingRequests->each(function ($request) {
            $this->assertEquals('pending', $request->status);
        });
    }

    public function test_approved_scope(): void
    {
        WithdrawalRequest::factory()->create(['status' => 'pending']);
        WithdrawalRequest::factory()->create(['status' => 'approved']);
        WithdrawalRequest::factory()->create(['status' => 'approved']);
        
        $approvedRequests = WithdrawalRequest::approved()->get();
        
        $this->assertCount(2, $approvedRequests);
        $approvedRequests->each(function ($request) {
            $this->assertEquals('approved', $request->status);
        });
    }

    public function test_rejected_scope(): void
    {
        WithdrawalRequest::factory()->create(['status' => 'approved']);
        WithdrawalRequest::factory()->create(['status' => 'rejected']);
        WithdrawalRequest::factory()->create(['status' => 'rejected']);
        
        $rejectedRequests = WithdrawalRequest::rejected()->get();
        
        $this->assertCount(2, $rejectedRequests);
        $rejectedRequests->each(function ($request) {
            $this->assertEquals('rejected', $request->status);
        });
    }

    public function test_processed_scope(): void
    {
        WithdrawalRequest::factory()->create(['status' => 'pending']);
        WithdrawalRequest::factory()->create(['status' => 'processed']);
        WithdrawalRequest::factory()->create(['status' => 'processed']);
        
        $processedRequests = WithdrawalRequest::processed()->get();
        
        $this->assertCount(2, $processedRequests);
        $processedRequests->each(function ($request) {
            $this->assertEquals('processed', $request->status);
        });
    }

    public function test_emergency_scope(): void
    {
        WithdrawalRequest::factory()->create(['type' => 'full']);
        WithdrawalRequest::factory()->create(['type' => 'emergency']);
        WithdrawalRequest::factory()->create(['type' => 'emergency']);
        
        $emergencyRequests = WithdrawalRequest::emergency()->get();
        
        $this->assertCount(2, $emergencyRequests);
        $emergencyRequests->each(function ($request) {
            $this->assertEquals('emergency', $request->type);
        });
    }

    public function test_full_scope(): void
    {
        WithdrawalRequest::factory()->create(['type' => 'partial']);
        WithdrawalRequest::factory()->create(['type' => 'full']);
        WithdrawalRequest::factory()->create(['type' => 'full']);
        
        $fullRequests = WithdrawalRequest::full()->get();
        
        $this->assertCount(2, $fullRequests);
        $fullRequests->each(function ($request) {
            $this->assertEquals('full', $request->type);
        });
    }

    public function test_partial_scope(): void
    {
        WithdrawalRequest::factory()->create(['type' => 'full']);
        WithdrawalRequest::factory()->create(['type' => 'partial']);
        WithdrawalRequest::factory()->create(['type' => 'partial']);
        
        $partialRequests = WithdrawalRequest::partial()->get();
        
        $this->assertCount(2, $partialRequests);
        $partialRequests->each(function ($request) {
            $this->assertEquals('partial', $request->type);
        });
    }

    public function test_is_pending_method(): void
    {
        $pendingRequest = WithdrawalRequest::factory()->create(['status' => 'pending']);
        $approvedRequest = WithdrawalRequest::factory()->create(['status' => 'approved']);
        
        $this->assertTrue($pendingRequest->isPending());
        $this->assertFalse($approvedRequest->isPending());
    }

    public function test_is_approved_method(): void
    {
        $pendingRequest = WithdrawalRequest::factory()->create(['status' => 'pending']);
        $approvedRequest = WithdrawalRequest::factory()->create(['status' => 'approved']);
        
        $this->assertFalse($pendingRequest->isApproved());
        $this->assertTrue($approvedRequest->isApproved());
    }

    public function test_is_rejected_method(): void
    {
        $approvedRequest = WithdrawalRequest::factory()->create(['status' => 'approved']);
        $rejectedRequest = WithdrawalRequest::factory()->create(['status' => 'rejected']);
        
        $this->assertFalse($approvedRequest->isRejected());
        $this->assertTrue($rejectedRequest->isRejected());
    }

    public function test_is_processed_method(): void
    {
        $approvedRequest = WithdrawalRequest::factory()->create(['status' => 'approved']);
        $processedRequest = WithdrawalRequest::factory()->create(['status' => 'processed']);
        
        $this->assertFalse($approvedRequest->isProcessed());
        $this->assertTrue($processedRequest->isProcessed());
    }

    public function test_is_emergency_method(): void
    {
        $fullRequest = WithdrawalRequest::factory()->create(['type' => 'full']);
        $emergencyRequest = WithdrawalRequest::factory()->create(['type' => 'emergency']);
        
        $this->assertFalse($fullRequest->isEmergency());
        $this->assertTrue($emergencyRequest->isEmergency());
    }

    public function test_is_full_withdrawal_method(): void
    {
        $partialRequest = WithdrawalRequest::factory()->create(['type' => 'partial']);
        $fullRequest = WithdrawalRequest::factory()->create(['type' => 'full']);
        
        $this->assertFalse($partialRequest->isFullWithdrawal());
        $this->assertTrue($fullRequest->isFullWithdrawal());
    }

    public function test_is_partial_withdrawal_method(): void
    {
        $fullRequest = WithdrawalRequest::factory()->create(['type' => 'full']);
        $partialRequest = WithdrawalRequest::factory()->create(['type' => 'partial']);
        
        $this->assertFalse($fullRequest->isPartialWithdrawal());
        $this->assertTrue($partialRequest->isPartialWithdrawal());
    }

    public function test_approve_method(): void
    {
        $request = WithdrawalRequest::factory()->create(['status' => 'pending']);
        
        $request->approve('Approved by admin');
        
        $this->assertEquals('approved', $request->status);
        $this->assertEquals('Approved by admin', $request->admin_notes);
        $this->assertNotNull($request->approved_at);
        $this->assertInstanceOf(Carbon::class, $request->approved_at);
    }

    public function test_reject_method(): void
    {
        $request = WithdrawalRequest::factory()->create(['status' => 'pending']);
        
        $request->reject('Insufficient documentation');
        
        $this->assertEquals('rejected', $request->status);
        $this->assertEquals('Insufficient documentation', $request->admin_notes);
        $this->assertNotNull($request->rejected_at);
        $this->assertInstanceOf(Carbon::class, $request->rejected_at);
    }

    public function test_process_method(): void
    {
        $request = WithdrawalRequest::factory()->create(['status' => 'approved']);
        
        $request->process();
        
        $this->assertEquals('processed', $request->status);
        $this->assertNotNull($request->processed_at);
        $this->assertInstanceOf(Carbon::class, $request->processed_at);
    }

    public function test_get_penalty_percentage_method(): void
    {
        $request = WithdrawalRequest::factory()->create([
            'amount' => 1000,
            'penalty_amount' => 150,
        ]);
        
        $penaltyPercentage = $request->getPenaltyPercentage();
        
        $this->assertEquals(15.0, $penaltyPercentage); // 150/1000 * 100 = 15%
    }

    public function test_get_penalty_percentage_with_zero_amount(): void
    {
        $request = WithdrawalRequest::factory()->create([
            'amount' => 0,
            'penalty_amount' => 150,
        ]);
        
        $penaltyPercentage = $request->getPenaltyPercentage();
        
        $this->assertEquals(0, $penaltyPercentage);
    }

    public function test_get_net_percentage_method(): void
    {
        $request = WithdrawalRequest::factory()->create([
            'amount' => 1000,
            'net_amount' => 850,
        ]);
        
        $netPercentage = $request->getNetPercentage();
        
        $this->assertEquals(85.0, $netPercentage); // 850/1000 * 100 = 85%
    }

    public function test_requires_approval_method(): void
    {
        $emergencyRequest = WithdrawalRequest::factory()->create(['type' => 'emergency']);
        $fullRequest = WithdrawalRequest::factory()->create(['type' => 'full']);
        
        $this->assertTrue($emergencyRequest->requiresApproval());
        $this->assertFalse($fullRequest->requiresApproval());
    }

    public function test_can_be_approved_method(): void
    {
        $pendingRequest = WithdrawalRequest::factory()->create(['status' => 'pending']);
        $approvedRequest = WithdrawalRequest::factory()->create(['status' => 'approved']);
        $rejectedRequest = WithdrawalRequest::factory()->create(['status' => 'rejected']);
        
        $this->assertTrue($pendingRequest->canBeApproved());
        $this->assertFalse($approvedRequest->canBeApproved());
        $this->assertFalse($rejectedRequest->canBeApproved());
    }

    public function test_can_be_processed_method(): void
    {
        $pendingRequest = WithdrawalRequest::factory()->create(['status' => 'pending']);
        $approvedRequest = WithdrawalRequest::factory()->create(['status' => 'approved']);
        $processedRequest = WithdrawalRequest::factory()->create(['status' => 'processed']);
        
        $this->assertFalse($pendingRequest->canBeProcessed());
        $this->assertTrue($approvedRequest->canBeProcessed());
        $this->assertFalse($processedRequest->canBeProcessed());
    }

    public function test_get_processing_time_method(): void
    {
        $request = WithdrawalRequest::factory()->create([
            'requested_at' => Carbon::now()->subDays(5),
            'processed_at' => Carbon::now(),
        ]);
        
        $processingTime = $request->getProcessingTime();
        
        $this->assertEquals(5, $processingTime); // 5 days
    }

    public function test_get_processing_time_for_unprocessed_request(): void
    {
        $request = WithdrawalRequest::factory()->create([
            'requested_at' => Carbon::now()->subDays(3),
            'processed_at' => null,
        ]);
        
        $processingTime = $request->getProcessingTime();
        
        $this->assertNull($processingTime);
    }

    public function test_get_status_color_method(): void
    {
        $pendingRequest = WithdrawalRequest::factory()->create(['status' => 'pending']);
        $approvedRequest = WithdrawalRequest::factory()->create(['status' => 'approved']);
        $rejectedRequest = WithdrawalRequest::factory()->create(['status' => 'rejected']);
        $processedRequest = WithdrawalRequest::factory()->create(['status' => 'processed']);
        
        $this->assertEquals('yellow', $pendingRequest->getStatusColor());
        $this->assertEquals('blue', $approvedRequest->getStatusColor());
        $this->assertEquals('red', $rejectedRequest->getStatusColor());
        $this->assertEquals('green', $processedRequest->getStatusColor());
    }

    public function test_fillable_attributes(): void
    {
        $fillable = [
            'user_id',
            'investment_id',
            'amount',
            'type',
            'status',
            'penalty_amount',
            'net_amount',
            'reason',
            'requested_at',
            'approved_at',
            'rejected_at',
            'processed_at',
            'admin_notes',
        ];
        
        $request = new WithdrawalRequest();
        
        $this->assertEquals($fillable, $request->getFillable());
    }

    public function test_casts_attributes_correctly(): void
    {
        $request = WithdrawalRequest::factory()->create([
            'amount' => '1000.50',
            'penalty_amount' => '150.25',
            'net_amount' => '850.25',
            'requested_at' => '2024-01-01 10:00:00',
        ]);
        
        $this->assertIsFloat($request->amount);
        $this->assertIsFloat($request->penalty_amount);
        $this->assertIsFloat($request->net_amount);
        $this->assertInstanceOf(Carbon::class, $request->requested_at);
    }

    public function test_validates_type_enum(): void
    {
        $validTypes = ['full', 'partial', 'emergency'];
        
        foreach ($validTypes as $type) {
            $request = WithdrawalRequest::factory()->create(['type' => $type]);
            $this->assertEquals($type, $request->type);
        }
    }

    public function test_validates_status_enum(): void
    {
        $validStatuses = ['pending', 'approved', 'rejected', 'processed'];
        
        foreach ($validStatuses as $status) {
            $request = WithdrawalRequest::factory()->create(['status' => $status]);
            $this->assertEquals($status, $request->status);
        }
    }
}