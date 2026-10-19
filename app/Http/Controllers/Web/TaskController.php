<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Http\Resources\TaskResource;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;

/**
 * The JSON endpoints the project page calls with jQuery AJAX. Each response carries the task's
 * data, its row already rendered by the same Blade component the page uses, and the project's
 * new task counts, so the page updates without a reload and without a second HTML template in JS.
 */
class TaskController extends Controller
{
    public function store(StoreTaskRequest $request, Project $project): JsonResponse
    {
        Gate::authorize('update', $project);
        $task = $project->tasks()->create($request->taskAttributes())->refresh()->loadCount('comments');

        return $this->taskResponse($task, $project, 201);
    }

    public function update(UpdateTaskRequest $request, Task $task): JsonResponse
    {
        Gate::authorize('update', $task);
        $task->update($request->validated());

        return $this->taskResponse($task->loadCount('comments'), $task->project);
    }

    public function destroy(Task $task): JsonResponse
    {
        Gate::authorize('delete', $task);
        $project = $task->project;
        $task->delete();

        return response()->json(['counts' => $project->statusCounts()]);
    }

    private function taskResponse(Task $task, Project $project, int $status = 200): JsonResponse
    {
        return response()->json([
            'task' => new TaskResource($task),
            'html' => Blade::render('<x-task-row :task="$task" />', ['task' => $task]),
            'counts' => $project->statusCounts(),
        ], $status);
    }
}
