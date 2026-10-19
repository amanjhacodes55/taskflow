<?php

namespace App\Http\Requests;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTaskRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'status' => ['nullable', Rule::enum(TaskStatus::class)],
            'priority' => ['nullable', Rule::enum(TaskPriority::class)],
            'due_at' => ['nullable', 'date'],
        ];
    }

    /** Leave out empty optional fields so the model's defaults (status "todo", priority "medium") apply. */
    public function taskAttributes(): array
    {
        return array_filter($this->validated(), fn ($value) => $value !== null);
    }
}
