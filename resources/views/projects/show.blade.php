@extends('layouts.app')
@section('title', $project->name)

@section('content')
<a href="{{ route('projects.index') }}" class="small text-decoration-none">&larr; All projects</a>
<h1 class="h3 mt-2 mb-1">{{ $project->name }}</h1>
@if ($project->description)
    <p class="text-muted">{{ $project->description }}</p>
@endif

<div class="card shadow-sm mb-3">
    <div class="card-body">
        <form id="task-form" class="row g-2 align-items-start" data-url="{{ route('tasks.store', $project) }}" novalidate>
            <div class="col-md-6">
                <input name="title" class="form-control" placeholder="What needs doing?" maxlength="255" required>
                <div class="invalid-feedback" data-error-for="title"></div>
            </div>
            <div class="col-md-2">
                <select name="priority" class="form-select" aria-label="Priority">
                    @foreach (\App\Enums\TaskPriority::cases() as $priority)
                        <option value="{{ $priority->value }}" @selected($priority === \App\Enums\TaskPriority::Medium)>{{ $priority->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <input name="due_at" type="date" class="form-control" aria-label="Due date">
                <div class="invalid-feedback" data-error-for="due_at"></div>
            </div>
            <div class="col-md-2 d-grid">
                <button class="btn btn-primary" type="submit">Add task</button>
            </div>
        </form>
    </div>
</div>

<div class="btn-group mb-3" role="group" aria-label="Filter tasks" id="filters">
    <button type="button" class="btn btn-outline-primary active" data-filter="all">All <span class="badge text-bg-light" data-count="all">{{ $counts['all'] }}</span></button>
    @foreach (\App\Enums\TaskStatus::cases() as $status)
        <button type="button" class="btn btn-outline-primary" data-filter="{{ $status->value }}">
            {{ $status->label() }} <span class="badge text-bg-light" data-count="{{ $status->value }}">{{ $counts[$status->value] }}</span>
        </button>
    @endforeach
</div>

<ul class="list-group shadow-sm" id="task-list">
    @foreach ($tasks as $task)
        <x-task-row :task="$task" />
    @endforeach
</ul>
<p class="text-center text-muted py-4 {{ $tasks->isEmpty() ? '' : 'd-none' }}" id="empty-state">No tasks yet. Add one above.</p>

<div class="toast-container position-fixed bottom-0 end-0 p-3">
    <div id="toast" class="toast text-bg-dark" role="status" aria-live="polite">
        <div class="toast-body"></div>
    </div>
</div>
@endsection

@push('scripts')
    <script src="{{ asset('js/tasks.js') }}"></script>
@endpush
