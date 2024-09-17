<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'description'];
    public function users()
    {
        return $this->belongsToMany(User::class)
                    ->withPivot('role', 'contribution_hours', 'last_activity')
                    ->withTimestamps();
    }
    public function tasks()
    {
        return $this->hasMany(Task::class);
    }
    public function getCreatedAtAttribute($value)
    {
        return \Carbon\Carbon::parse($value)->format('d/m/Y H:i');
    }

    public function getUpdatedAtAttribute($value)
    {
        return \Carbon\Carbon::parse($value)->format('d/m/Y H:i');
    }

    public function tasksWithFilter($priority = null, $status = null, $project_id = null)
    {
        $query = Task::query();

        if ($project_id) {
            $query->whereRelation('project', 'id', $project_id);
        }

        if ($status) {
            $query->where('status', $status);
        }

        if ($priority) {
            $query->where('priority', $priority);
        }

        return $query->get();
    }

}
