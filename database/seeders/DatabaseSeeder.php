<?php

namespace Database\Seeders;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Comment;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * A demo account with a few realistic projects, so the app has something to show straight away.
     * Log in as demo@taskflow.test / password.
     */
    public function run(): void
    {
        $demo = User::factory()->create([
            'name' => 'Demo User',
            'email' => 'demo@taskflow.test',
        ]);

        $projects = [
            'Website redesign' => [
                'description' => 'New landing pages, pricing page and blog for the product launch.',
                'tasks' => [
                    ['Design the new pricing page', 'done', 'high', -6],
                    ['Write copy for the features section', 'in_progress', 'medium', 1],
                    ['Add FAQ structured data for SEO', 'todo', 'medium', 4],
                    ['Compress hero images', 'todo', 'low', 9],
                    ['Set up 301 redirects from old URLs', 'todo', 'high', -1],
                    ['Review the mobile layout', 'in_progress', 'medium', null],
                ],
            ],
            'Mobile app launch' => [
                'description' => 'Everything needed to ship v1 to the Play Store.',
                'tasks' => [
                    ['Prepare Play Store screenshots', 'todo', 'medium', 3],
                    ['Write the privacy policy', 'done', 'high', -10],
                    ['Fix crash on Android 13 login', 'in_progress', 'high', 0],
                    ['Internal testing round with the team', 'todo', 'medium', 7],
                    ['Translate the onboarding screens', 'todo', 'low', null],
                ],
            ],
            'Hiring' => [
                'description' => 'Interviews and onboarding for the new developers.',
                'tasks' => [
                    ['Shortlist PHP developer resumes', 'done', 'medium', -3],
                    ['Schedule technical interviews', 'in_progress', 'high', 2],
                    ['Prepare the Laravel take-home task', 'todo', 'medium', 5],
                    ['Order laptops for new joiners', 'todo', 'low', 12],
                ],
            ],
        ];

        foreach ($projects as $name => $data) {
            $project = Project::factory()->for($demo, 'owner')->create([
                'name' => $name,
                'description' => $data['description'],
            ]);

            foreach ($data['tasks'] as [$title, $status, $priority, $dueInDays]) {
                $task = $project->tasks()->create([
                    'title' => $title,
                    'status' => TaskStatus::from($status),
                    'priority' => TaskPriority::from($priority),
                    'due_at' => $dueInDays === null ? null : now()->addDays($dueInDays)->setTime(17, 0),
                ]);

                Comment::factory()->count(fake()->numberBetween(0, 2))->for($task)->for($demo, 'author')->create();
            }
        }
    }
}
