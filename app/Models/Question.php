<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
       protected $table = 'questions';
    protected $fillable = [
        'type',
        'reference_id',
        'question',
        'option1',
        'option2',
        'option3',
        'option4',
        'correct_option',
    ];

    // روابط هوشمند بسته به نوع سوال
    public function reference()
    {
        return match ($this->type) {
            'lesson'  => $this->belongsTo(Lesson::class, 'reference_id'),
            'chapter' => $this->belongsTo(Chapter::class, 'reference_id'),
            'course'  => $this->belongsTo(Course::class, 'reference_id'),
            default   => null,
        };
    }
}
