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

        $response = $this->getJson(route('task.index'));

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

    public function test_it_returns_tasks_of_the_week_of_the_specified_date(): void
    {
        $monday = Carbon::now()->startOfWeek(CarbonInterface::MONDAY);
        $tuesday = $monday->copy()->addDay();

        Task::factory()->create(['scheduled_day' => $monday]);
        Task::factory()->create(['scheduled_day' => $tuesday]);

        $response = $this->getJson(route('task.index', ['date' => now()->addWeek()->toDateString()]));

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [],
                'startOfWeek',
                'endOfWeek',
            ]);
    }
}
