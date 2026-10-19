<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\TaskStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Http\Resources\ProjectResource;
use App\Models\Project;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

class ProjectController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $projects = $request->user()->projects()
            ->withCount(['tasks', 'tasks as open_tasks_count' => fn (Builder $q) => $q->where('status', '!=', TaskStatus::Done->value)])
            ->latest()
            ->paginate(perPage($request));

        return ProjectResource::collection($projects);
    }

    public function store(StoreProjectRequest $request): ProjectResource
    {
        $project = $request->user()->projects()->create($request->validated());

        return new ProjectResource($project);
    }

    public function show(Project $project): ProjectResource
    {
        Gate::authorize('view', $project);

        return new ProjectResource($project->loadCount('tasks'));
    }

    public function update(UpdateProjectRequest $request, Project $project): ProjectResource
    {
        Gate::authorize('update', $project);
        $project->update($request->validated());

        return new ProjectResource($project);
    }

    public function destroy(Project $project): Response
    {
        Gate::authorize('delete', $project);
        $project->delete();

        return response()->noContent();
    }
}
