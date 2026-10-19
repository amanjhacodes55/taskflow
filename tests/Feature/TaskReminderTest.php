<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Notifications\TaskDueSoon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class TaskReminderTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_reminds_owners_about_open_tasks_due_within_a_day_only_once(): void
    {
        Notification::fake();
        $owner = User::factory()->create();
        $project = Project::factory()->for($owner, 'owner')->create();

        $dueSoon = Task::factory()->todo()->for($project)->dueIn(3)->create();
        Task::factory()->done()->for($project)->dueIn(3)->create();     // already done
        Task::factory()->todo()->for($project)->dueIn(72)->create();    // not due yet
        Task::factory()->todo()->for($project)->dueIn(-2)->create();    // already overdue
        Task::factory()->todo()->for($project)->create(['due_at' => null]);

        $this->artisan('tasks:remind')->expectsOutput('Sent 1 reminder.')->assertSuccessful();

        Notification::assertSentTo($owner, TaskDueSoon::class, fn (TaskDueSoon $n) => $n->task->is($dueSoon));
        Notification::assertCount(1);
        $this->assertNotNull($dueSoon->fresh()->reminded_at);

        // Running again must not remind about the same task a second time.
        $this->artisan('tasks:remind')->expectsOutput('Sent 0 reminders.');
        Notification::assertCount(1);
    }

    public function test_the_reminder_is_scheduled_hourly(): void
    {
        $this->artisan('schedule:list')->expectsOutputToContain('tasks:remind');
    }
}
