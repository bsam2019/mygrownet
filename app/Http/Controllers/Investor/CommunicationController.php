<?php

namespace App\Http\Controllers\Investor;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Investor\Concerns\RequiresInvestorAuth;
use App\Domain\Investor\Services\InvestorCommunicationService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CommunicationController extends Controller
{
    use RequiresInvestorAuth;

    public function __construct(
        private InvestorCommunicationService $communicationService
    ) {}

    // =====================================================
    // Q&A PORTAL
    // =====================================================

    public function questions(Request $request)
    {
        $investor = $this->requireInvestorAuth();
        
        if ($investor instanceof \Illuminate\Http\RedirectResponse) {
            return $investor;
        }

        $investorId = $investor->getId();

        $publishedQuestions = $this->communicationService->getPublishedQuestions();
        $featuredQuestions = $this->communicationService->getFeaturedQuestions();
        $myQuestions = $this->communicationService->getInvestorQuestions($investorId);

        return Inertia::render('Investor/Questions', [
            'investor' => [
                'id' => $investor->getId(),
                'name' => $investor->getName(),
                'email' => $investor->getEmail(),
            ],
            'publishedQuestions' => $publishedQuestions->map(fn($q) => [
                ...$q->toArray(),
                'has_upvoted' => $q->hasBeenUpvotedBy($investorId),
                'author_name' => $q->is_public ? 'Investor' : 'Anonymous Investor',
            ]),
            'featuredQuestions' => $featuredQuestions,
            'myQuestions' => $myQuestions,
            'categories' => [
                'financial' => 'Financial',
                'operations' => 'Operations',
                'strategy' => 'Strategy',
                'dividends' => 'Dividends',
                'governance' => 'Governance',
                'legal' => 'Legal',
                'general' => 'General',
            ],
            'activePage' => 'questions',
            'unreadMessages' => 0,
            'unreadAnnouncements' => 0,
        ]);
    }

    public function submitQuestion(Request $request)
    {
        $investor = $this->getAuthenticatedInvestor();
        
        if (!$investor) {
            return redirect()->route('investor.login');
        }

        $request->validate([
            'subject' => 'required|string|max:255',
            'question' => 'required|string|max:2000',
            'category' => 'required|in:financial,operations,strategy,dividends,governance,legal,general',
            'is_anonymous' => 'boolean',
        ]);

        $this->communicationService->submitQuestion(
            $investor->getId(),
            $request->subject,
            $request->question,
            $request->category,
            $request->is_anonymous ?? false
        );

        return back()->with('success', 'Your question has been submitted and will be reviewed.');
    }

    public function upvoteQuestion(Request $request, int $questionId)
    {
        $investor = $this->getAuthenticatedInvestor();

        if (!$investor) {
            return response()->json(['error' => 'Not authenticated.'], 401);
        }

        $result = $this->communicationService->upvoteQuestion($questionId, $investor->getId());
        
        return response()->json([
            'success' => $result,
            'message' => $result ? 'Question upvoted.' : 'Already upvoted.',
        ]);
    }

    public function removeUpvote(Request $request, int $questionId)
    {
        $investor = $this->getAuthenticatedInvestor();

        if (!$investor) {
            return response()->json(['error' => 'Not authenticated.'], 401);
        }

        $result = $this->communicationService->removeUpvote($questionId, $investor->getId());
        
        return response()->json([
            'success' => $result,
            'message' => $result ? 'Upvote removed.' : 'No upvote found.',
        ]);
    }

    // =====================================================
    // FEEDBACK
    // =====================================================

    public function feedback(Request $request)
    {
        $investor = $this->requireInvestorAuth();
        
        if ($investor instanceof \Illuminate\Http\RedirectResponse) {
            return $investor;
        }

        $investorId = $investor->getId();
        $investorAccount = \App\Models\InvestorAccount::find($investorId);

        $myFeedback = $investorAccount 
            ? $this->communicationService->getInvestorFeedback($investorAccount->id)
            : collect();

        return Inertia::render('Investor/Feedback', [
            'investor' => [
                'id' => $investor->getId(),
                'name' => $investor->getName(),
                'email' => $investor->getEmail(),
            ],
            'myFeedback' => $myFeedback,
            'feedbackTypes' => [
                'suggestion' => 'Suggestion',
                'complaint' => 'Complaint',
                'praise' => 'Praise',
                'question' => 'Question',
                'other' => 'Other',
            ],
            'activePage' => 'feedback',
            'unreadMessages' => 0,
            'unreadAnnouncements' => 0,
        ]);
    }

    public function submitFeedback(Request $request)
    {
        $investor = $this->getAuthenticatedInvestor();
        
        if (!$investor) {
            return redirect()->route('investor.login');
        }

        $request->validate([
            'feedback_type' => 'required|in:suggestion,complaint,praise,question,other',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:5000',
            'rating' => 'nullable|integer|min:1|max:5',
        ]);

        $this->communicationService->submitFeedback(
            $investor->getId(),
            $request->feedback_type,
            $request->subject,
            $request->message,
            $request->rating
        );

        return back()->with('success', 'Thank you for your feedback!');
    }

    // =====================================================
    // SURVEYS
    // =====================================================

    public function surveys(Request $request)
    {
        $investor = $this->requireInvestorAuth();
        
        if ($investor instanceof \Illuminate\Http\RedirectResponse) {
            return $investor;
        }

        $investorId = $investor->getId();

        $activeSurveys = $this->communicationService->getActiveSurveys();
        $myResponses = $this->communicationService->getInvestorSurveyResponses($investorId);

        // Mark completed surveys
        $activeSurveys = $activeSurveys->map(function ($survey) use ($investorId) {
            $survey->is_completed = $this->communicationService->hasCompletedSurvey($survey->id, $investorId);
            return $survey;
        });

        return Inertia::render('Investor/Surveys', [
            'investor' => [
                'id' => $investor->getId(),
                'name' => $investor->getName(),
                'email' => $investor->getEmail(),
            ],
            'activeSurveys' => $activeSurveys,
            'myResponses' => $myResponses,
            'activePage' => 'surveys',
            'unreadMessages' => 0,
            'unreadAnnouncements' => 0,
        ]);
    }

    public function submitSurvey(Request $request, int $surveyId)
    {
        $investor = $this->getAuthenticatedInvestor();
        
        if (!$investor) {
            return redirect()->route('investor.login');
        }

        $request->validate([
            'answers' => 'required|array',
        ]);

        try {
            $this->communicationService->submitSurveyResponse(
                $surveyId,
                $investor->getId(),
                $request->answers
            );

            return back()->with('success', 'Survey submitted successfully!');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
