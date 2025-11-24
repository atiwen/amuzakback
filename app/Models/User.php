<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;


use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasApiTokens;
    use HasFactory;

    protected $fillable = [
        'name',
        'phone',
        'grade',
        'password',
        'favorite_subjects',
        'role',
        'subscription_type'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->id = $model->generateCustomId();
        });
    }

    public function generateCustomId()
    {
        $lastId = static::max('id');
        $newId = $lastId + 1;
        return $newId;
    }

    public function lessons()
    {
        return $this->belongsToMany(Lesson::class)
            ->withPivot('is_passed', 'is_quiz_passed')
            ->withTimestamps();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    public function courses()
    {
        return $this->belongsToMany(Course::class, 'course_user')
            ->withTimestamps()
            ->withPivot('enrolled_at', 'completed_at');
    }

    public function lessonAnswers()
    {
        return $this->hasMany(LessonAnswer::class);
    }
}
