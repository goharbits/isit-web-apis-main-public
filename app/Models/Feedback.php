<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Feedback extends Model
{
    protected $fillable = ['user_id', 'customer_id', 'service_id', 'message', 'stars', 'request_id','parent_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function request()
    {
        return $this->belongsTo(Request::class);
    }

    public function parentFeedback()
    {
        return $this->belongsTo(Feedback::class, 'parent_id');
    }

    public function replies()
    {
        return $this->hasOne(Feedback::class, 'parent_id');
    }
}
