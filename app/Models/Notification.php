<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $fillable = ['title', 'description', 'user_id', 'request_id', 'sender_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function request()
    {
        return $this->belongsTo(Request::class);
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

      public function sender_feedback()
    {
         return $this->hasMany(Feedback::class, 'customer_id', 'sender_id')
                ->whereNotNull('parent_id');
    }
}
