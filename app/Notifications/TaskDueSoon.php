<?php

namespace App\Notifications;

use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/** Sent to a project's owner when one of its open tasks is due within a day. Queued, so sending never slows the scheduler. */
class TaskDueSoon extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Task $task) {}

    /** @return array<int, string> */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Due soon: {$this->task->title}")
            ->greeting("Hi {$notifiable->name},")
            ->line("“{$this->task->title}” is due {$this->task->due_at->diffForHumans()}.")
            ->action('Open the project', route('projects.show', $this->task->project_id))
            ->line('Mark it as done in TaskFlow once it’s finished.');
    }

    /** What's stored in the notifications table. */
    public function toArray(object $notifiable): array
    {
        return [
            'task_id' => $this->task->id,
            'project_id' => $this->task->project_id,
            'title' => $this->task->title,
            'due_at' => $this->task->due_at?->toIso8601String(),
        ];
    }
}
