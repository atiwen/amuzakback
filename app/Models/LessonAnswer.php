<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LessonAnswer extends Model
{
    protected $fillable = [
        'question_id', 'text', 'is_correct',
    ];

    // هر جواب متعلق به یک سوال است
    public function question()
    {
        return $this->belongsTo(LessonQuestion::class, 'question_id');
    }
}
