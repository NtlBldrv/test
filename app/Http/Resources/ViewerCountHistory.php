<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

class ViewerCountHistory extends Resource
{
    public function toArray($request)
    {
        return [
            'viewer_count' => $this->viewer_count,
            'created_at'   => $this->created_at,
            'updated_at'   => $this->updated_at,
            'stream'       => $this->stream,
        ];
    }
}
