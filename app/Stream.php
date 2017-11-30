<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Stream extends Model
{
    protected $fillable = ['channel_id', 'stream_id', 'viewer_count', 'active'];

    public function game()
    {
        return $this->belongsTo('App\Game');
    }

    public function service()
    {
        return $this->belongsTo('App\StreamingService');
    }
}
