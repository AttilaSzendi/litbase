<?php

namespace Feature\Task;

use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskDestroyTest extends TestCase
{
    use RefreshDatabase;

    public function test_task_can_be_deleted(): void
    {
        /** @var Task $task */
        $task = Task::factory()->create();

        $response = $this->deleteJson(route('task.destroy', $task->id));

        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);

        $response->assertOk();
    }
}
