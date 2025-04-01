<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Ramsey\Collection\Collection;

/**
 * @property int id
 * @property string name
 * @property int estimated_minutes
 * @property Carbon completed_at
 * @property int priority_id
 * @property Carbon scheduled_day
 * @property Carbon created_at
 * @property Carbon updated_at
 * @property Collection|User[] users
 */
class Task extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'estimated_minutes', 'completed_at', 'priority_id', 'scheduled_day'];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    protected function casts(): array
    {
        return [
            'completed_at' => 'datetime',
            'scheduled_day' => 'date',
        ];
    }
}
