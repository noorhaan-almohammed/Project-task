<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_admin'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
         'is_admin'
    ];
    protected $attributes = [
        'is_admin' => false,
    ];
    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_admin' => 'boolean'
    ];
    public function projects()
    {
        return $this->belongsToMany(Project::class)
                    ->withPivot('role', 'contribution_hours', 'last_activity')
                    ->withTimestamps();
    }
    public function userTasks()
    {
        return $this->hasMany(Task::class);
    }
    public function latestTask(){
        return $this->hasOne(Task::class)
                    ->latestOfMany('due_date');
    }
    public function oldestTask(){
       return $this->hasOne(Task::class)
                    ->oldestOfMany('start_date');
    }
    public function longTimeHigPriorityhTask(){
        return $this->hasOne(Task::class)
        ->ofMany(['execute_time' => 'max'],
        function($q){
            $q->where('priority','LIKE','High');
        });
    }
    public function projectTasks(){
        return $this->hasManyThrough(
            Task::class,            // related
            ProjectUser::class,     //  through
            'user_id',             //  project_user
            'project_id',         //  tasks
            'id',                  //  users
            'project_id'     // project_user
        );
    }
    public function getCreatedAtAttribute($value)
    {
        return \Carbon\Carbon::parse($value)->format('d/m/Y H:i');
    }

    public function getUpdatedAtAttribute($value)
    {
        return \Carbon\Carbon::parse($value)->format('d/m/Y H:i');
    }

    /**
     * Get the identifier that will be stored in the JWT subject claim.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims you want to add to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }
}
