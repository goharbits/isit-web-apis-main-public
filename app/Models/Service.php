<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $fillable = ['name', 'currency', 'price', 'user_id', 'corporate_id'];


    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function corporate()
    {
        return $this->belongsTo(User::class, 'corporate_id');
    }
    public function feedback()
    {
        return $this->hasMany(Feedback::class);
    }
}
