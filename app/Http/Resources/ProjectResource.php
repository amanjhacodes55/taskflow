<?php

namespace App\Http\Resources;

use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Project */
class ProjectResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'tasks_count' => $this->whenCounted('tasks'),
            'open_tasks_count' => $this->whenCounted('openTasks'),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
