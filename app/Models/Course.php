<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    protected $fillable = [
        'title','imgurl','description','grade', 'intro_contents', 'is_pro', 'tags'
    ];

    protected $casts = [
        'intro_contents' => 'array',
        'tags' => 'array',
        'is_pro' => 'boolean',
    ];

    // یک دوره چند فصل دارد
    public function chapters()
    {
        return $this->hasMany(Chapter::class);
    }

    // کاربران ثبت نام کرده در این دوره
    public function users()
    {
        return $this->belongsToMany(User::class, 'course_user')
            ->withTimestamps()
            ->withPivot('enrolled_at', 'completed_at');
    }
}
