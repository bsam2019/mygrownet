<?php

namespace App\Http\Controllers\GrowFinance;

use App\Domain\GrowFinance\Services\NaturalLanguageQueryService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NlpQueryController extends Controller
{
    public function __construct(
        private NaturalLanguageQueryService $nlpService,
    ) {}

    public function ask(Request $request): JsonResponse
    {
        $businessId = $request->user()->id;
        $question = $request->input('question', '');

        if (empty(trim($question))) {
            return response()->json(['error' => 'Question is required'], 422);
        }

        $result = $this->nlpService->query($businessId, $question);
        return response()->json($result);
    }
}
