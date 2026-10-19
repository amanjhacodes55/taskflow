<?php

namespace Tests\Feature\Web;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_projects_page_lists_my_projects(): void
    {
        $user = User::factory()->create();
        Project::factory()->for($user, 'owner')->create(['name' => 'Website redesign']);
        Project::factory()->create(['name' => 'Someone else’s project']);

        $this->actingAs($user)->get('/projects')
            ->assertOk()
            ->assertSee('Website redesign')
            ->assertDontSee('Someone else’s project');
    }

    public function test_i_can_create_a_project_from_the_form(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/projects', ['name' => 'Launch']);

        $project = Project::firstWhere('name', 'Launch');
        $response->assertRedirect("/projects/{$project->id}");
    }

    public function test_the_project_page_shows_its_tasks_escaped(): void
    {
        $user = User::factory()->create();
        $project = Project::factory()->for($user, 'owner')->create();
        Task::factory()->for($project)->create(['title' => '<script>alert(1)</script>']);

        $this->actingAs($user)->get("/projects/{$project->id}")
            ->assertOk()
            ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)
            ->assertDontSee('<script>alert(1)</script>', false);
    }

    public function test_i_cannot_open_someone_elses_project(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/projects/'.Project::factory()->create()->id)
            ->assertForbidden();
    }
}
