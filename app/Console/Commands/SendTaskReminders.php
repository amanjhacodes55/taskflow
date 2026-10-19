<?php

namespace App\Console\Commands;

use App\Models\Task;
use App\Notifications\TaskDueSoon;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;

#[Signature('tasks:remind')]
#[Description('Remind project owners about open tasks due in the next 24 hours')]
class SendTaskReminders extends Command
{
    public function handle(): int
    {
        $sent = 0;

        // chunkById keeps memory flat however many tasks there are, and is safe while we update rows.
        Task::dueSoon()->with('project.owner')->chunkById(100, function (Collection $tasks) use (&$sent) {
            foreach ($tasks as $task) {
                $task->project->owner->notify(new TaskDueSoon($task));
                $task->forceFill(['reminded_at' => now()])->save();   // never remind about the same task twice
                $sent++;
            }
        });

        $this->info("Sent {$sent} ".str('reminder')->plural($sent).'.');

        return self::SUCCESS;
    }
}
