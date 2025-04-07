<?php

namespace App\Http\Requests;

use App\Models\Task;
use App\Rules\Weekday;
use Illuminate\Foundation\Http\FormRequest;

class TaskRequest extends FormRequest
{
    const EIGHT_HOURS_IN_MINUTES = 480;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'estimated_minutes' => 'required|integer|min:1',
            'completed_at' => 'nullable|date',
            'priority_id' => 'required|integer|min:1|max:5',
            'scheduled_day' => ['required', 'date', new Weekday()],
            'assigned_user_ids' => ['nullable', 'array'],
            'assigned_user_ids.*' => ['integer', 'distinct', 'exists:users,id'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $this->validateWorkload($validator);
        });
    }

    private function validateWorkload($validator): void
    {
        $assignedUserIds = $this->input('assigned_user_ids', []);
        $scheduledDay = $this->input('scheduled_day');
        $estimatedMinutes = $this->input('estimated_minutes');

        $errors = [];

        foreach ($assignedUserIds as $userId) {
            $totalMinutes = Task::query()
                ->where('id', '<>', $this->route('task')?->id)
                ->whereHas('assignedUsers', function ($query) use ($userId) {
                    $query->where('users.id', $userId);
                })->where('scheduled_day', $scheduledDay)
                ->sum('estimated_minutes');

            if ($totalMinutes + $estimatedMinutes > self::EIGHT_HOURS_IN_MINUTES) {
                $errors[] = [
                    "userId" => $userId,
                    "message" => "A felhasználó számára nincs elég kapacitás ezen a napon.",
                    "remainingMinutes" => self::EIGHT_HOURS_IN_MINUTES - $totalMinutes,
                ];
            }
        }

        if (!empty($errors)) {
            foreach ($errors as $error) {
                $validator->errors()->add('assignee_overload', $error);
            }
        }
    }
}
