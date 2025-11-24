<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Chapter extends Model
{
    protected $fillable = [
        'course_id', 'title', 'order',
    ];

    // هر فصل متعلق به یک دوره است
    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    // یک فصل چند درس دارد
    public function lessons()
    {
        return $this->hasMany(Lesson::class);
    }
}
