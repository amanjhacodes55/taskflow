<?php

namespace Tests\Feature\Web;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** The JSON endpoints the project page calls with jQuery. */
class TaskAjaxTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Project $project;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->project = Project::factory()->for($this->user, 'owner')->create();
    }

    public function test_adding_a_task_returns_its_rendered_row_and_new_counts(): void
    {
        $this->actingAs($this->user)
            ->postJson("/projects/{$this->project->id}/tasks", ['title' => 'Call the client', 'priority' => 'high'])
            ->assertCreated()
            ->assertJsonPath('task.title', 'Call the client')
            ->assertJsonPath('task.priority', 'high')
            ->assertJsonPath('counts.all', 1)
            ->assertJsonPath('counts.todo', 1)
            ->assertJson(fn ($json) => $json->where('html', fn (string $html) => str_contains($html, 'Call the client'))->etc());
    }

    public function test_validation_errors_come_back_as_422_json(): void
    {
        $this->actingAs($this->user)
            ->postJson("/projects/{$this->project->id}/tasks", ['title' => ''])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('title');
    }

    public function test_ticking_a_task_marks_it_done(): void
    {
        $task = Task::factory()->todo()->for($this->project)->create();

        $this->actingAs($this->user)
            ->patchJson("/tasks/{$task->id}", ['status' => 'done'])
            ->assertOk()
            ->assertJsonPath('task.status', 'done')
            ->assertJsonPath('counts.done', 1);
    }

    public function test_deleting_a_task_returns_the_new_counts(): void
    {
        $task = Task::factory()->for($this->project)->create();

        $this->actingAs($this->user)
            ->deleteJson("/tasks/{$task->id}")
            ->assertOk()
            ->assertJsonPath('counts.all', 0);
        $this->assertModelMissing($task);
    }

    public function test_i_cannot_change_someone_elses_task(): void
    {
        $this->actingAs($this->user)
            ->patchJson('/tasks/'.Task::factory()->create()->id, ['status' => 'done'])
            ->assertForbidden();
    }
}
