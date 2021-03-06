<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

class Stream extends Resource
{
    /** {@inheritdoc} */
    public function toArray($request)
    {
        return [
            'id'           => $this->id,
            'game'         => $this->game,
            'channel_id'   => $this->channel_id,
            'stream_id'    => $this->stream_id,
            'service'      => $this->service,
            'viewer_count' => $this->viewer_count,
            'created_at'   => $this->created_at,
            'updated_at'   => $this->updated_at,
        ];
    }
}
