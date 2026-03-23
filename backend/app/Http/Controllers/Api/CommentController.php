<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\ContentItem;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    // GET /content-items/{contentItem}/comments
    public function index(ContentItem $contentItem)
    {
        $comments = $contentItem->comments()
            ->with('user:id,name,role')
            ->orderBy('created_at')
            ->get();

        return response()->json($comments);
    }

    // POST /content-items/{contentItem}/comments
    public function store(Request $request, ContentItem $contentItem)
    {
        $data = $request->validate(['body' => 'required|string|max:2000']);

        $comment = $contentItem->comments()->create([
            'user_id' => $request->user()->id,
            'body'    => $data['body'],
        ]);

        $comment->load('user:id,name,role');

        return response()->json($comment, 201);
    }

    // DELETE /content-items/{contentItem}/comments/{comment}
    public function destroy(ContentItem $contentItem, Comment $comment)
    {
        if ($comment->content_item_id !== $contentItem->id) {
            return response()->json(['message' => 'Not found'], 404);
        }

        if ($comment->user_id !== request()->user()->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $comment->delete();

        return response()->json(null, 204);
    }
}
