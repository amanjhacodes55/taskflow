<?php

namespace Tests\Feature\Api;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProjectTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = Sanctum::actingAs(User::factory()->create());
    }

    public function test_it_lists_only_my_projects_with_task_counts(): void
    {
        $mine = Project::factory()->for($this->user, 'owner')->create();
        Task::factory()->count(2)->todo()->for($mine)->create();
        Task::factory()->done()->for($mine)->create();
        Project::factory()->create();   // someone else's

        $this->getJson('/api/v1/projects')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $mine->id)
            ->assertJsonPath('data.0.tasks_count', 3)
            ->assertJsonPath('data.0.open_tasks_count', 2)
            ->assertJsonStructure(['data', 'links', 'meta' => ['current_page', 'total']]);
    }

    public function test_it_creates_a_project(): void
    {
        $this->postJson('/api/v1/projects', ['name' => 'Launch', 'description' => 'Ship v1'])
            ->assertCreated()
            ->assertJsonPath('data.name', 'Launch');

        $this->assertDatabaseHas('projects', ['name' => 'Launch', 'user_id' => $this->user->id]);
    }

    public function test_a_project_needs_a_name(): void
    {
        $this->postJson('/api/v1/projects', ['name' => ''])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('name');
    }

    public function test_it_updates_and_deletes_my_project(): void
    {
        $project = Project::factory()->for($this->user, 'owner')->create();

        $this->patchJson("/api/v1/projects/{$project->id}", ['name' => 'Renamed'])
            ->assertOk()
            ->assertJsonPath('data.name', 'Renamed');

        $this->deleteJson("/api/v1/projects/{$project->id}")->assertNoContent();
        $this->assertModelMissing($project);
    }

    public function test_i_cannot_see_or_change_someone_elses_project(): void
    {
        $theirs = Project::factory()->create();

        $this->getJson("/api/v1/projects/{$theirs->id}")->assertForbidden();
        $this->patchJson("/api/v1/projects/{$theirs->id}", ['name' => 'Mine now'])->assertForbidden();
        $this->deleteJson("/api/v1/projects/{$theirs->id}")->assertForbidden();
        $this->assertModelExists($theirs);
    }

    public function test_a_missing_project_is_a_404(): void
    {
        $this->getJson('/api/v1/projects/999')->assertNotFound();
    }
}
