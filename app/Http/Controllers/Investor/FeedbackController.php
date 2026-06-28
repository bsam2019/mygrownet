<?php

namespace App\Http\Controllers\Investor;

use App\Http\Controllers\Controller;
use App\Domain\Investor\Services\InvestorFeedbackService;
use App\Models\InvestorAccount;
use Illuminate\Http\Request;
use Inertia\Inertia;

class FeedbackController extends Controller
{
    public function __construct(
        private readonly InvestorFeedbackService $feedbackService
    ) {}

    /**
     * Display feedback page with surveys and polls
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $investorAccount = InvestorAccount::where('user_id', $user->id)->first();

        if (!$investorAccount) {
            return redirect()->route('investor.dashboard')
                ->with('error', 'Investor account not found.');
        }

        $myFeedback = $this->feedbackService->getInvestorFeedback($investorAccount->id);
        $activeSurveys = $this->feedbackService->getActiveSurveys($investorAccount->id);
        $activePolls = $this->feedbackService->getActivePolls($investorAccount->id);

        return Inertia::render('Investor/Feedback', [
            'myFeedback' => $myFeedback,
            'activeSurveys' => $activeSurveys,
            'activePolls' => $activePolls,
        ]);
    }

    /**
     * Submit feedback
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'feedback_type' => 'required|in:suggestion,concern,compliment,complaint',
            'category' => 'required|in:portal,communication,reporting,governance,other',
            'subject' => 'required|string|max:255',
            'feedback' => 'required|string|max:5000',
            'satisfaction_rating' => 'nullable|integer|min:1|max:5',
        ]);

        $investorAccount = InvestorAccount::where('user_id', $request->user()->id)->firstOrFail();
        
        $this->feedbackService->submitFeedback($investorAccount->id, $validated);

        return back()->with('success', 'Thank you for your feedback!');
    }

    /**
     * Submit survey response
     */
    public function submitSurvey(Request $request, int $surveyId)
    {
        $validated = $request->validate([
            'responses' => 'required|array',
        ]);

        $investorAccount = InvestorAccount::where('user_id', $request->user()->id)->firstOrFail();

        try {
            $this->feedbackService->submitSurveyResponse($surveyId, $investorAccount->id, $validated['responses']);
            return back()->with('success', 'Survey submitted successfully!');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Submit poll vote
     */
    public function submitPollVote(Request $request, int $pollId)
    {
        $validated = $request->validate([
            'selected_options' => 'required|array|min:1',
            'selected_options.*' => 'integer',
        ]);

        $investorAccount = InvestorAccount::where('user_id', $request->user()->id)->firstOrFail();

        try {
            $this->feedbackService->submitPollVote($pollId, $investorAccount->id, $validated['selected_options']);
            return back()->with('success', 'Vote submitted!');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
