<?php

namespace App\Http\Controllers\Task;

use App\Http\Controllers\Controller;
use App\Http\Resources\TaskResource;
use App\Models\Task;

class TaskDestroyController extends Controller
{
    public function __invoke(Task $task): TaskResource
    {
        $task->delete();

        return new TaskResource($task);
    }
}
