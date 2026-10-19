@extends('layouts.app')
@section('title', 'Projects')

@section('content')
<div class="row g-4">
    <div class="col-lg-8">
        <h1 class="h3 mb-3">Your projects</h1>
        @forelse ($projects as $project)
            <div class="card shadow-sm mb-3">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="flex-grow-1">
                        <a href="{{ route('projects.show', $project) }}" class="h5 text-decoration-none">{{ $project->name }}</a>
                        @if ($project->description)
                            <p class="text-muted small mb-1">{{ $project->description }}</p>
                        @endif
                        <span class="small text-muted">{{ $project->open_tasks_count }} open · {{ $project->tasks_count }} total</span>
                    </div>
                    <form method="POST" action="{{ route('projects.destroy', $project) }}"
                          onsubmit="return confirm('Delete this project and all its tasks?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm btn-outline-danger">Delete</button>
                    </form>
                </div>
            </div>
        @empty
            <div class="text-center text-muted py-5 border rounded bg-white">No projects yet. Create your first one.</div>
        @endforelse
    </div>

    <div class="col-lg-4">
        <div class="card shadow-sm">
            <div class="card-body">
                <h2 class="h5">New project</h2>
                <form method="POST" action="{{ route('projects.store') }}">
                    @csrf
                    <div class="mb-2">
                        <input name="name" value="{{ old('name') }}" placeholder="Project name" required
                               class="form-control @error('name') is-invalid @enderror">
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-2">
                        <textarea name="description" rows="3" placeholder="Description (optional)"
                                  class="form-control @error('description') is-invalid @enderror">{{ old('description') }}</textarea>
                        @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <button class="btn btn-primary w-100">Create project</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
