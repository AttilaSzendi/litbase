<?php

namespace Feature\Task;

use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskStoreTest extends TestCase
{
    use RefreshDatabase;

    const FOUR_HOURS_IN_MINUTES = 240;
    const FIVE_HOURS_IN_MINUTES = 300;

    public function test_task_can_be_stored(): void
    {
        $task = Task::factory()
            ->make(['scheduled_day' => Carbon::now()->startOfWeek(CarbonInterface::MONDAY)])
            ->toArray();

        $response = $this->postJson(route('task.store'), $task);

        $this->assertDatabaseHas('tasks', $task);

        $response->assertCreated();
    }

    public function test_task_can_be_stored_with_scheduled_day_on_the_weekend(): void
    {
        $task = Task::factory()->make(['scheduled_day' => now()->next('Saturday')])->toArray();

        $response = $this->postJson(route('task.store'), $task);

        $response->assertUnprocessable();
    }

    public function test_it_allows_task_creation_when_users_capacity_is_under_8_hours()
    {
        /** @var Task $task */
        $task = Task::factory()
            ->has(User::factory()->count(1))
                ->create([
                    'estimated_minutes' => self::FOUR_HOURS_IN_MINUTES,
                    'scheduled_day' => '2025-04-02',
                ]);

        $response = $this->postJson(route('task.store'), [
            'name' => 'Új feladat',
            'estimated_minutes' => self::FOUR_HOURS_IN_MINUTES,
            'scheduled_day' => '2025-04-02',
            'assigned_user_ids' => [$task->users()->first()->id],
            'priority_id' => 1,
        ])->dump();

        $this->assertDatabaseHas('task_user', [
            'task_id' => $task->id,
            'user_id' => $task->users()->first()->id,
        ]);

        $this->assertDatabaseHas('task_user', [
            'task_id' => $task->id + 1,
            'user_id' => $task->users()->first()->id,
        ]);

        $response->assertCreated();
    }

    public function test_it_rejects_task_creation_if_any_user_capacity_exceeds_8_hours()
    {
        /** @var Task $task */
        $task = Task::factory()
            ->has(User::factory()->count(1))
            ->create([
                'estimated_minutes' => self::FOUR_HOURS_IN_MINUTES,
                'scheduled_day' => '2025-04-02',
            ]);

        $response = $this->postJson(route('task.store'), [
            'name' => 'Túl sok munka',
            'estimated_minutes' => self::FIVE_HOURS_IN_MINUTES,
            'scheduled_day' => '2025-04-02',
            'assigned_user_ids' => [$task->users()->first()->id],
            'priority_id' => 1,
        ]);

        $response->assertUnprocessable()
        ->assertJsonValidationErrors(['assigned_user_ids']);
    }
}
