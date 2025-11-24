<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Certificate extends Model
{
              protected $table = 'certificates';
              protected $fillable = [
    'user_id',
    'course_id',
    'certificate_code',
    'issued_at',
];

}
