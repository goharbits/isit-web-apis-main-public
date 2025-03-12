<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    protected $fillable = ['plan_id', 'role_id', 'type'];


    public function role()
    {
        return $this->belongsTo(Role::class);
    }
}
