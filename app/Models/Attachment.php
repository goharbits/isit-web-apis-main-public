<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attachment extends Model
{
    protected $fillable = ['path', 'message_id', 'type'];


    public function message()
    {
        return $this->belongsTo(Message::class);
    }
}
