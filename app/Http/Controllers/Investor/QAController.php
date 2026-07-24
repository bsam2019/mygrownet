<?php

namespace App\Http\Controllers\Investor;

use App\Http\Controllers\Controller;
use App\Domain\Investor\Services\InvestorQAService;
use App\Domain\Investor\Repositories\InvestorAccountRepositoryInterface;
use Illuminate\Http\Request;
use Inertia\Inertia;

class QAController extends Controller
{
    public function __construct(
        private readonly InvestorQAService $qaService,
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

        $myQuestions = $this->qaService->getInvestorQuestions($investorAccount->getId());
        $publicQuestions = $this->qaService->getPublicQuestions($request->input('category'));
        $categories = $this->qaService->getCategoryCounts();

        return Inertia::render('Investor/QA', [
            'myQuestions' => $myQuestions,
            'publicQuestions' => $publicQuestions,
            'categories' => $categories,
            'selectedCategory' => $request->input('category'),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category' => 'required|in:financial,operations,strategy,governance,other',
            'subject' => 'required|string|max:255',
            'question' => 'required|string|max:2000',
        ]);

        $investorAccount = $this->accountRepository->findByUserId($request->user()->id);

        if (!$investorAccount) {
            return back()->withErrors(['error' => 'Investor account not found.']);
        }

        $this->qaService->submitQuestion($investorAccount->getId(), $validated);

        return back()->with('success', 'Your question has been submitted. We will respond shortly.');
    }

    public function upvote(Request $request, int $questionId)
    {
        $this->qaService->upvoteQuestion($questionId);

        return response()->json(['success' => true]);
    }

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
