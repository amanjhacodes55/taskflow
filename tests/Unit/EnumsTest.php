<?php

namespace Tests\Unit;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use PHPUnit\Framework\TestCase;

class EnumsTest extends TestCase
{
    public function test_statuses_have_labels_and_colours(): void
    {
        $this->assertSame('In progress', TaskStatus::InProgress->label());
        $this->assertSame('success', TaskStatus::Done->color());
        $this->assertSame(TaskStatus::Todo, TaskStatus::from('todo'));
        $this->assertNull(TaskStatus::tryFrom('finished'));
    }

    public function test_priorities_have_labels_and_colours(): void
    {
        $this->assertSame('High', TaskPriority::High->label());
        $this->assertSame('danger', TaskPriority::High->color());
        $this->assertCount(3, TaskPriority::cases());
    }
}
