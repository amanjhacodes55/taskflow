<?php

namespace Tests\Feature\Api;

use App\Enums\TaskStatus;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TaskTest extends TestCase
{
    use RefreshDatabase;

    private Project $project;

    protected function setUp(): void
    {
        parent::setUp();
        $user = Sanctum::actingAs(User::factory()->create());
        $this->project = Project::factory()->for($user, 'owner')->create();
    }

    public function test_it_creates_a_task_with_default_status_and_priority(): void
    {
        $this->postJson("/api/v1/projects/{$this->project->id}/tasks", ['title' => 'Write the README'])
            ->assertCreated()
            ->assertJsonPath('data.title', 'Write the README')
            ->assertJsonPath('data.status', 'todo')
            ->assertJsonPath('data.priority', 'medium');
    }

    public function test_task_fields_are_validated(): void
    {
        $this->postJson("/api/v1/projects/{$this->project->id}/tasks", [
            'title' => '',
            'status' => 'finished',
            'priority' => 'urgent',
            'due_at' => 'not-a-date',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['title', 'status', 'priority', 'due_at']);
    }

    public function test_it_filters_searches_and_paginates(): void
    {
        Task::factory()->count(3)->todo()->for($this->project)->create();
        Task::factory()->count(2)->done()->for($this->project)->create();
        Task::factory()->todo()->for($this->project)->create(['title' => 'Fix the login bug']);

        $this->getJson("/api/v1/projects/{$this->project->id}/tasks?status=done")
            ->assertOk()
            ->assertJsonCount(2, 'data');

        $this->getJson("/api/v1/projects/{$this->project->id}/tasks?search=login")
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'Fix the login bug');

        $this->getJson("/api/v1/projects/{$this->project->id}/tasks?per_page=4")
            ->assertOk()
            ->assertJsonCount(4, 'data')
            ->assertJsonPath('meta.total', 6)
            ->assertJsonPath('meta.last_page', 2);
    }

    public function test_it_sorts_by_due_date(): void
    {
        $later = Task::factory()->for($this->project)->dueIn(48)->create();
        $sooner = Task::factory()->for($this->project)->dueIn(2)->create();

        $this->getJson("/api/v1/projects/{$this->project->id}/tasks?sort=due_at")
            ->assertJsonPath('data.0.id', $sooner->id);

        $this->getJson("/api/v1/projects/{$this->project->id}/tasks?sort=-due_at")
            ->assertJsonPath('data.0.id', $later->id);
    }

    public function test_it_shows_updates_and_deletes_a_task(): void
    {
        $task = Task::factory()->todo()->for($this->project)->create();

        $this->getJson("/api/v1/tasks/{$task->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $task->id)
            ->assertJsonPath('data.comments', []);

        $this->patchJson("/api/v1/tasks/{$task->id}", ['status' => 'done'])
            ->assertOk()
            ->assertJsonPath('data.status', 'done');
        $this->assertSame(TaskStatus::Done, $task->fresh()->status);

        $this->deleteJson("/api/v1/tasks/{$task->id}")->assertNoContent();
        $this->assertModelMissing($task);
    }

    public function test_an_overdue_open_task_is_flagged(): void
    {
        $task = Task::factory()->todo()->for($this->project)->dueIn(-5)->create();

        $this->getJson("/api/v1/tasks/{$task->id}")->assertJsonPath('data.is_overdue', true);
    }

    public function test_i_cannot_touch_tasks_in_someone_elses_project(): void
    {
        $theirs = Task::factory()->create();

        $this->getJson("/api/v1/projects/{$theirs->project_id}/tasks")->assertForbidden();
        $this->postJson("/api/v1/projects/{$theirs->project_id}/tasks", ['title' => 'x'])->assertForbidden();
        $this->getJson("/api/v1/tasks/{$theirs->id}")->assertForbidden();
        $this->patchJson("/api/v1/tasks/{$theirs->id}", ['status' => 'done'])->assertForbidden();
        $this->deleteJson("/api/v1/tasks/{$theirs->id}")->assertForbidden();
    }
}
