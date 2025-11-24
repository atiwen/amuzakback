<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class options extends Model
{
    protected $table = 'options';
    protected $fillable = [ 'name','type','value','date'];
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
