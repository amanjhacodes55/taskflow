<?php

namespace Tests\Feature\Api;

use App\Models\Comment;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CommentTest extends TestCase
{
    use RefreshDatabase;

    public function test_i_can_comment_on_my_task_and_list_comments(): void
    {
        $user = Sanctum::actingAs(User::factory()->create(['name' => 'Aman']));
        $task = Task::factory()->for(Project::factory()->for($user, 'owner'))->create();
        Comment::factory()->for($task)->for($user, 'author')->create(['body' => 'First']);

        $this->postJson("/api/v1/tasks/{$task->id}/comments", ['body' => 'Looks good'])
            ->assertCreated()
            ->assertJsonPath('data.body', 'Looks good')
            ->assertJsonPath('data.author.name', 'Aman');

        $this->getJson("/api/v1/tasks/{$task->id}/comments")
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.body', 'First');
    }

    public function test_a_comment_needs_a_body(): void
    {
        $user = Sanctum::actingAs(User::factory()->create());
        $task = Task::factory()->for(Project::factory()->for($user, 'owner'))->create();

        $this->postJson("/api/v1/tasks/{$task->id}/comments", ['body' => ''])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('body');
    }

    public function test_i_cannot_comment_on_someone_elses_task(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->postJson('/api/v1/tasks/'.Task::factory()->create()->id.'/comments', ['body' => 'Hi'])
            ->assertForbidden();
    }
}
