<?php

namespace Feature\Task;

use App\Models\Task;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_a_list_of_tasks_grouped_by_scheduled_day(): void
    {
        $monday = Carbon::now()->startOfWeek(CarbonInterface::MONDAY);
        $tuesday = $monday->copy()->addDay();

        Task::factory()->create(['scheduled_day' => $monday]);
        Task::factory()->create(['scheduled_day' => $tuesday]);

        $response = $this->getJson(route('task.index'))->dump();

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    $monday->toDateString(),
                    $tuesday->toDateString(),
                ],
                'startOfWeek',
                'endOfWeek',
            ]);
    }
}
