<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ViewerCountHistory extends Model
{
    protected $table    = 'viewer_count_history';
    protected $fillable = ['stream_id', 'viewer_count'];

    public function stream()
    {
        return $this->belongsTo('App\Stream');
    }
}
