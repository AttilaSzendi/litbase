<?php

namespace App\Http\Controllers\Task;

use App\Http\Controllers\Controller;
use App\Http\Requests\TaskRequest;
use App\Http\Resources\TaskResource;
use App\Models\Task;

class TaskUpdateController extends Controller
{
    public function __invoke(TaskRequest $request, Task $task): TaskResource
    {
        $task->update($request->validated());

        $task->assignedUsers()->sync($request->input('assigned_user_ids'));

        return new TaskResource($task);
    }
}
