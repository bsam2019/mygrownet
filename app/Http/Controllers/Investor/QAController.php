<?php

namespace App\Http\Controllers\Investor;

use App\Http\Controllers\Controller;
use App\Domain\Investor\Services\InvestorQAService;
use App\Models\InvestorAccount;
use Illuminate\Http\Request;
use Inertia\Inertia;

class QAController extends Controller
{
    public function __construct(
        private readonly InvestorQAService $qaService
    ) {}

    /**
     * Display Q&A page
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $investorAccount = InvestorAccount::where('user_id', $user->id)->first();

        if (!$investorAccount) {
            return redirect()->route('investor.dashboard')
                ->with('error', 'Investor account not found.');
        }

        $myQuestions = $this->qaService->getInvestorQuestions($investorAccount->id);
        $publicQuestions = $this->qaService->getPublicQuestions($request->input('category'));
        $categories = $this->qaService->getCategoryCounts();

        return Inertia::render('Investor/QA', [
            'myQuestions' => $myQuestions,
            'publicQuestions' => $publicQuestions,
            'categories' => $categories,
            'selectedCategory' => $request->input('category'),
        ]);
    }

    /**
     * Submit a new question
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'category' => 'required|in:financial,operations,strategy,governance,other',
            'subject' => 'required|string|max:255',
            'question' => 'required|string|max:2000',
        ]);

        $investorAccount = InvestorAccount::where('user_id', $request->user()->id)->firstOrFail();
        
        $this->qaService->submitQuestion($investorAccount->id, $validated);

        return back()->with('success', 'Your question has been submitted. We will respond shortly.');
    }

    /**
     * Upvote a public question
     */
    public function upvote(Request $request, int $questionId)
    {
        $this->qaService->upvoteQuestion($questionId);

        return response()->json(['success' => true]);
    }

    /**
     * Get public FAQ
     */
    public function faq(Request $request)
    {
        $category = $request->input('category');
        $questions = $this->qaService->getPublicQuestions($category);
        $categories = $this->qaService->getCategoryCounts();

        return response()->json([
            'questions' => $questions,
            'categories' => $categories,
        ]);
    }
}
