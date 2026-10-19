@props(['task'])

<li class="list-group-item task-row d-flex align-items-center gap-3 {{ $task->status === \App\Enums\TaskStatus::Done ? 'is-done' : '' }}"
    data-id="{{ $task->id }}" data-status="{{ $task->status->value }}">
    <input class="form-check-input js-toggle mt-0" type="checkbox" aria-label="Mark as done"
           @checked($task->status === \App\Enums\TaskStatus::Done)>
    <div class="flex-grow-1">
        <div class="task-title fw-semibold">{{ $task->title }}</div>
        <div class="small text-muted d-flex flex-wrap gap-2 mt-1">
            <span class="badge text-bg-{{ $task->status->color() }}">{{ $task->status->label() }}</span>
            <span class="badge text-bg-{{ $task->priority->color() }}">{{ $task->priority->label() }}</span>
            @if ($task->due_at)
                <span class="{{ $task->is_overdue ? 'text-danger fw-semibold' : '' }}">
                    Due {{ $task->due_at->format('j M Y') }}{{ $task->is_overdue ? ' · overdue' : '' }}
                </span>
            @endif
            @if ($task->comments_count)
                <span>{{ $task->comments_count }} {{ Str::plural('comment', $task->comments_count) }}</span>
            @endif
        </div>
    </div>
    <select class="form-select form-select-sm w-auto js-status" aria-label="Status">
        @foreach (\App\Enums\TaskStatus::cases() as $status)
            <option value="{{ $status->value }}" @selected($task->status === $status)>{{ $status->label() }}</option>
        @endforeach
    </select>
    <button type="button" class="btn btn-sm btn-outline-danger btn-delete js-delete" aria-label="Delete task">Delete</button>
</li>
