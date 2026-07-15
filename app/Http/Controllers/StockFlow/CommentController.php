<?php

namespace App\Http\Controllers\StockFlow;

use App\Http\Controllers\Controller;
use App\Domain\StockFlow\Services\CommentService;
use App\Domain\StockFlow\Exceptions\CommentNotFoundException;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function __construct(
        private CommentService $commentService,
    ) {}

    public function index(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|string',
            'id' => 'required|integer',
        ]);

        $companyId = $request->session()->get('stockflow_company_id');

        $comments = $this->commentService->getComments($companyId, $validated['type'], $validated['id']);

        $userIds = array_unique(array_map(fn($c) => $c['sa_user_id'], $comments));
        $users = \App\Infrastructure\Persistence\Eloquent\StockFlow\SaUserModel::whereIn('id', $userIds)
            ->get()
            ->keyBy('id');

        $comments = array_map(function ($c) use ($users) {
            $user = $users->get($c['sa_user_id']);
            $c['user'] = $user ? ['id' => $user->id, 'name' => $user->name, 'email' => $user->email] : null;
            return $c;
        }, $comments);

        return response()->json(['comments' => $comments]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|string',
            'id' => 'required|integer',
            'body' => 'required|string|max:2000',
        ]);

        $companyId = $request->session()->get('stockflow_company_id');
        $userId = $request->user('stockflow')->id;

        $comment = $this->commentService->addComment(
            $companyId,
            $validated['type'],
            (int) $validated['id'],
            $userId,
            $validated['body'],
        );

        return response()->json(['comment' => $comment->toArray()], 201);
    }

    public function update(Request $request, int $commentId)
    {
        $validated = $request->validate([
            'body' => 'required|string|max:2000',
        ]);

        try {
            $comment = $this->commentService->updateComment($commentId, $validated['body']);
            return response()->json(['comment' => $comment->toArray()]);
        } catch (CommentNotFoundException $e) {
            return response()->json(['error' => $e->getMessage()], 404);
        }
    }

    public function destroy(int $commentId)
    {
        try {
            $this->commentService->deleteComment($commentId);
            return response()->json(['success' => true]);
        } catch (CommentNotFoundException $e) {
            return response()->json(['error' => $e->getMessage()], 404);
        }
    }
}
