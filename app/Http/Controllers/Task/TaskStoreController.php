<?php

namespace App\Http\Controllers\Task;

use App\Http\Controllers\Controller;
use App\Http\Requests\TaskRequest;
use App\Http\Resources\TaskResource;
use App\Models\Task;

class TaskStoreController extends Controller
{
    public function __invoke(TaskRequest $request): TaskResource
    {
        $task = Task::query()->create($request->validated());

        $task->users()->sync($request->input('assigned_user_ids'));

        return new TaskResource($task);
    }
}
