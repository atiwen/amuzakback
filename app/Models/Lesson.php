<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lesson extends Model
{
    protected $table = 'lessons';

    protected $fillable = [
        'chapter_id',
        'title',
        'order',
    ];

    // هر درس متعلق به یک فصل است
    public function chapter()
    {
        return $this->belongsTo(Chapter::class);
    }
    
    // یک درس چند بخش دارد
    public function sections()
    {
        return $this->hasMany(Section::class);
    }
    
    public function users()
    {
        return $this->belongsToMany(User::class)
            ->withPivot('is_passed', 'is_quiz_passed')
            ->withTimestamps();
    }

    // سوالات درس
    public function questions()
    {
        return $this->hasMany(LessonQuestion::class);
    }
}
