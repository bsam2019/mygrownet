<?php

namespace App\Http\Controllers\Investor;

use App\Http\Controllers\Controller;
use App\Domain\Investor\Services\InvestorFeedbackService;
use App\Domain\Investor\Repositories\InvestorAccountRepositoryInterface;
use Illuminate\Http\Request;
use Inertia\Inertia;

class FeedbackController extends Controller
{
    public function __construct(
        private readonly InvestorFeedbackService $feedbackService,
        private readonly InvestorAccountRepositoryInterface $accountRepository
    ) {}

    public function index(Request $request)
    {
        $user = $request->user();
        $investorAccount = $this->accountRepository->findByUserId($user->id);

        if (!$investorAccount) {
            return redirect()->route('investor.dashboard')
                ->with('error', 'Investor account not found.');
        }

        $myFeedback = $this->feedbackService->getInvestorFeedback($investorAccount->getId());
        $activeSurveys = $this->feedbackService->getActiveSurveys($investorAccount->getId());
        $activePolls = $this->feedbackService->getActivePolls($investorAccount->getId());

        return Inertia::render('Investor/Feedback', [
            'myFeedback' => $myFeedback,
            'activeSurveys' => $activeSurveys,
            'activePolls' => $activePolls,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'feedback_type' => 'required|in:suggestion,concern,compliment,complaint',
            'category' => 'required|in:portal,communication,reporting,governance,other',
            'subject' => 'required|string|max:255',
            'feedback' => 'required|string|max:5000',
            'satisfaction_rating' => 'nullable|integer|min:1|max:5',
        ]);

        $investorAccount = $this->accountRepository->findByUserId($request->user()->id);

        if (!$investorAccount) {
            return back()->withErrors(['error' => 'Investor account not found.']);
        }

        $this->feedbackService->submitFeedback($investorAccount->getId(), $validated);

        return back()->with('success', 'Thank you for your feedback!');
    }

    public function submitSurvey(Request $request, int $surveyId)
    {
        $validated = $request->validate([
            'responses' => 'required|array',
        ]);

        $investorAccount = $this->accountRepository->findByUserId($request->user()->id);

        if (!$investorAccount) {
            return back()->withErrors(['error' => 'Investor account not found.']);
        }

        try {
            $this->feedbackService->submitSurveyResponse($surveyId, $investorAccount->getId(), $validated['responses']);
            return back()->with('success', 'Survey submitted successfully!');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function submitPollVote(Request $request, int $pollId)
    {
        $validated = $request->validate([
            'selected_options' => 'required|array|min:1',
            'selected_options.*' => 'integer',
        ]);

        $investorAccount = $this->accountRepository->findByUserId($request->user()->id);

        if (!$investorAccount) {
            return back()->withErrors(['error' => 'Investor account not found.']);
        }

        try {
            $this->feedbackService->submitPollVote($pollId, $investorAccount->getId(), $validated['selected_options']);
            return back()->with('success', 'Vote submitted!');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
