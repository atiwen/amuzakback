<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LessonQuestion extends Model
{
    protected $fillable = [
        'lesson_id', 'question_text',
    ];

    // هر سوال متعلق به یک درس است
    public function lesson()
    {
        return $this->belongsTo(Lesson::class);
    }

    // جواب‌های سوال
    public function answers()
    {
        return $this->hasMany(LessonAnswer::class, 'question_id');
    }
}
