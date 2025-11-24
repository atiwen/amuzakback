<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Content extends Model
{
protected $table = 'contents';
    
    protected $fillable = [
    'course_id',
    'title',
    'body',
    'order',
];
}
