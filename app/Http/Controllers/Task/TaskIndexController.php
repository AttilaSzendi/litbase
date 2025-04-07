<?php

namespace App\Http\Controllers\Task;

use App\Http\Controllers\Controller;
use App\Http\Resources\TaskResource;
use App\Models\Task;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TaskIndexController extends Controller
{
    public const DAY_COUNT_UNTIL_FRIDAY = 4;

    public function __invoke(Request $request): JsonResponse
    {
        $date = $request->query('date', now()->toDateString());
        $startOfWeek = Carbon::parse($date)->startOfWeek(CarbonInterface::MONDAY);
        $endOfWeek = (clone $startOfWeek)->addDays(self::DAY_COUNT_UNTIL_FRIDAY);

        $tasks = Task::query()
            ->whereBetween('scheduled_day', [$startOfWeek, $endOfWeek])
            ->orderBy('scheduled_day')
            ->with('assignedUsers')
            ->get()
            ->groupBy(fn ($task) => Carbon::parse($task->scheduled_day)->toDateString())
            ->map(fn ($tasks) => TaskResource::collection($tasks));

        return response()->json([
            'data' => $tasks,
            'startOfWeek' => $startOfWeek->toDateString(),
            'endOfWeek' => $endOfWeek->toDateString(),
        ]);
    }
}
