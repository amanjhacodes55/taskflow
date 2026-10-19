<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Http\Resources\TaskResource;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

class TaskController extends Controller
{
    /** GET /projects/{project}/tasks?status=todo&priority=high&search=api&sort=-due_at&per_page=20 */
    public function index(Request $request, Project $project): AnonymousResourceCollection
    {
        Gate::authorize('view', $project);

        $tasks = $project->tasks()
            ->filter($request->only('status', 'priority', 'search'))
            ->sortBy($request->query('sort'))
            ->withCount('comments')
            ->paginate(perPage($request))
            ->withQueryString();

        return TaskResource::collection($tasks);
    }

    public function store(StoreTaskRequest $request, Project $project): TaskResource
    {
        Gate::authorize('update', $project);
        $task = $project->tasks()->create($request->taskAttributes());

        return new TaskResource($task->refresh());
    }

    public function show(Task $task): TaskResource
    {
        Gate::authorize('view', $task);

        return new TaskResource($task->load('comments.author')->loadCount('comments'));
    }

    public function update(UpdateTaskRequest $request, Task $task): TaskResource
    {
        Gate::authorize('update', $task);
        $task->update($request->validated());

        return new TaskResource($task);
    }

    public function destroy(Task $task): Response
    {
        Gate::authorize('delete', $task);
        $task->delete();

        return response()->noContent();
    }
}
