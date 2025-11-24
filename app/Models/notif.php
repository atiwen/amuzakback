<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class notif extends Model
{
    protected $table = 'notifs';
    protected $fillable = [ 'user_id','massage','type','link','aderss','is_user'];
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
