<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class reg_req extends Model
{
   
    protected $table = 'reg_req'; 
    protected $fillable = ["phone","email","code","status","key","expire"];
    public $timestamps = false;
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
