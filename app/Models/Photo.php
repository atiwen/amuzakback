<?php
// app/Models/Photo.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Photo extends Model
{
    use HasFactory;

    // اگر نام جدول با نام مدل فرق دارد، باید مشخص شود
    protected $table = 'photos';

    protected $fillable = ['path', 'user_id', 'is_cerd'];
}
