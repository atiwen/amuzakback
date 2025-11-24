<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class supp_mass extends Model
{
      
    protected $table = 'supp_mass'; 
    protected $fillable = ["mass","user_id","type"];
    public $timestamps = true;
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
