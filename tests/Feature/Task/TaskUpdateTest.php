<?php

namespace Feature\Task;

use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskUpdateTest extends TestCase
{
    use RefreshDatabase;

    const FOUR_HOURS_IN_MINUTES = 240;
    const NINE_HOURS_IN_MINUTES = 540;

    public function test_task_can_be_updated(): void
    {
        /** @var Task $task */
        $task = Task::factory()->create();

        $input = Task::factory()
            ->make(['scheduled_day' => Carbon::now()->startOfWeek(CarbonInterface::MONDAY)])
            ->toArray();

        $response = $this->patchJson(route('task.update', $task->id), $input);

        $this->assertDatabaseHas('tasks', $input);

        $response->assertOk();
    }

    public function test_it_rejects_task_update_if_any_user_capacity_exceeds_8_hours()
    {
        /** @var Task $task */
        $task = Task::factory()
            ->has(User::factory()->count(1), 'assignedUsers')
            ->create([
                'estimated_minutes' => self::FOUR_HOURS_IN_MINUTES,
                'scheduled_day' => '2025-04-02',
            ]);

        $response = $this->patchJson(route('task.update', $task->id), [
            'name' => 'Túl sok munka',
            'estimated_minutes' => self::NINE_HOURS_IN_MINUTES,
            'scheduled_day' => '2025-04-02',
            'assigned_user_ids' => [$task->assignedUsers()->first()->id],
            'priority_id' => 1,
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['assignee_overload']);
    }
}
