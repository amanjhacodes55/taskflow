<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCommentRequest;
use App\Http\Resources\CommentResource;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

class CommentController extends Controller
{
    public function index(Request $request, Task $task): AnonymousResourceCollection
    {
        Gate::authorize('view', $task);

        return CommentResource::collection(
            $task->comments()->with('author')->oldest()->paginate(perPage($request))
        );
    }

    public function store(StoreCommentRequest $request, Task $task): CommentResource
    {
        Gate::authorize('view', $task);

        $comment = $task->comments()->make($request->validated());
        $comment->author()->associate($request->user());
        $comment->save();

        return new CommentResource($comment->load('author'));
    }
}
