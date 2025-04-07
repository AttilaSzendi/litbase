<?php

namespace Database\Seeders;

use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Seeder;

class TasksTableSeeder extends Seeder
{
    public function run(): void
    {
        $startOfWeek = now()->startOfWeek();

        Task::factory()->count(14)->create([
            'scheduled_day' => fn() => $startOfWeek->copy()->addDays(rand(0, 6)),
        ])->each(function ($task) {
            $task->assignedUsers()->attach(
                User::query()->inRandomOrder()->take(rand(0, 4))->pluck('id')->toArray()
            );
        });
    }
}
