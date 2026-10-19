<?php

namespace App\Models;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use Database\Factories\TaskFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['title', 'description', 'status', 'priority', 'due_at'])]
class Task extends Model
{
    /** @use HasFactory<TaskFactory> */
    use HasFactory;

    /** The columns a list of tasks may be sorted by. */
    public const SORTABLE = ['due_at', 'created_at', 'title'];

    protected $attributes = [
        'status' => 'todo',
        'priority' => 'medium',
    ];

    protected function casts(): array
    {
        return [
            'status' => TaskStatus::class,
            'priority' => TaskPriority::class,
            'due_at' => 'datetime',
            'reminded_at' => 'datetime',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    /** A task is overdue when its due date has passed and it isn't done. */
    protected function isOverdue(): Attribute
    {
        return Attribute::get(
            fn () => $this->due_at !== null && $this->due_at->isPast() && $this->status !== TaskStatus::Done
        );
    }

    /**
     * Narrow a list of tasks by the filters a request asked for: status, priority and a title search.
     *
     * @param  array{status?: string, priority?: string, search?: string}  $filters
     */
    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['status'] ?? null, fn (Builder $q, string $status) => $q->where('status', $status))
            ->when($filters['priority'] ?? null, fn (Builder $q, string $priority) => $q->where('priority', $priority))
            ->when($filters['search'] ?? null, fn (Builder $q, string $search) => $q->where('title', 'like', "%{$search}%"));
    }

    /** Sort by one of the SORTABLE columns; a leading "-" sorts descending, e.g. "-due_at". */
    public function scopeSortBy(Builder $query, ?string $sort): Builder
    {
        $column = ltrim((string) $sort, '-');
        if (! in_array($column, self::SORTABLE, true)) {
            return $query->latest();
        }

        // Tasks without a due date go last, whichever direction we sort in.
        if ($column === 'due_at') {
            $query->orderByRaw('due_at is null');
        }

        return $query->orderBy($column, str_starts_with((string) $sort, '-') ? 'desc' : 'asc');
    }

    /** Open tasks due within the next day that haven't been reminded about yet. */
    public function scopeDueSoon(Builder $query): Builder
    {
        return $query
            ->whereNull('reminded_at')
            ->where('status', '!=', TaskStatus::Done->value)
            ->whereBetween('due_at', [now(), now()->addDay()]);
    }
}
