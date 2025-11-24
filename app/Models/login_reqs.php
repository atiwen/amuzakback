<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class login_reqs extends Model
{
   
    protected $table = 'login_req'; 
    protected $fillable = ["phone","code"];

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
