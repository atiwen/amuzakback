<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChapterQuizQuestion extends Model
{
    protected $fillable = ['chapter_id', 'question', 'option1', 'option2', 'option3', 'option4', 'correct_option'];

    public function chapter() {
        return $this->belongsTo(Chapter::class);
    }
}

