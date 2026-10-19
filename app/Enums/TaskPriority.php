<?php

namespace App\Enums;

enum TaskPriority: string
{
    case Low = 'low';
    case Medium = 'medium';
    case High = 'high';

    public function label(): string
    {
        return ucfirst($this->value);
    }

    /** Bootstrap colour used for the priority badge. */
    public function color(): string
    {
        return match ($this) {
            self::Low => 'info',
            self::Medium => 'primary',
            self::High => 'danger',
        };
    }
}
