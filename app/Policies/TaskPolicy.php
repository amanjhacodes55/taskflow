<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;

/** Whoever owns a task's project may see, change and delete the task. */
class TaskPolicy
{
    public function view(User $user, Task $task): bool
    {
        return $user->id === $task->project->user_id;
    }

    public function update(User $user, Task $task): bool
    {
        return $user->id === $task->project->user_id;
    }

    public function delete(User $user, Task $task): bool
    {
        return $user->id === $task->project->user_id;
    }
}
