<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserToken extends Model
{
    protected $fillable = [
        'user_id',
        'refresh_token',
        'user_agent',
        'last_online_at',
    ];
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->id = $model->generateCustomId();
        });
    }
    public function generateCustomId()
    {
        $lastId = static::max('id'); 
        $newId = $lastId + 1; 
        return $newId;
    }
}
