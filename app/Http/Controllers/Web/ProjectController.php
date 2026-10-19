<?php

namespace App\Http\Controllers\Web;

use App\Enums\TaskStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProjectRequest;
use App\Models\Project;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class ProjectController extends Controller
{
    public function index(Request $request): View
    {
        $projects = $request->user()->projects()
            ->withCount(['tasks', 'tasks as open_tasks_count' => fn (Builder $q) => $q->where('status', '!=', TaskStatus::Done->value)])
            ->latest()
            ->get();

        return view('projects.index', compact('projects'));
    }

    public function store(StoreProjectRequest $request): RedirectResponse
    {
        $project = $request->user()->projects()->create($request->validated());

        return redirect()->route('projects.show', $project)->with('status', 'Project created.');
    }

    public function show(Project $project): View
    {
        Gate::authorize('view', $project);

        $tasks = $project->tasks()->withCount('comments')->orderByRaw('due_at is null')->orderBy('due_at')->latest()->get();

        return view('projects.show', [
            'project' => $project,
            'tasks' => $tasks,
            'counts' => $project->statusCounts(),
        ]);
    }

    public function destroy(Project $project): RedirectResponse
    {
        Gate::authorize('delete', $project);
        $project->delete();

        return redirect()->route('projects.index')->with('status', 'Project deleted.');
    }
}
