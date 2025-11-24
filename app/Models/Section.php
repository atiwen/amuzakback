<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Section extends Model
{
    protected $fillable = [
        'lesson_id', 'contents', 'order',
    ];

    protected $casts = [
        'contents' => 'array',
    ];

    // هر بخش متعلق به یک درس است
    public function lesson()
    {
        return $this->belongsTo(Lesson::class);
    }
}
