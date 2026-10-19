<?php

namespace App\Enums;

enum TaskStatus: string
{
    case Todo = 'todo';
    case InProgress = 'in_progress';
    case Done = 'done';

    public function label(): string
    {
        return match ($this) {
            self::Todo => 'To do',
            self::InProgress => 'In progress',
            self::Done => 'Done',
        };
    }

    /** Bootstrap colour used for the status badge. */
    public function color(): string
    {
        return match ($this) {
            self::Todo => 'secondary',
            self::InProgress => 'warning',
            self::Done => 'success',
        };
    }
}
