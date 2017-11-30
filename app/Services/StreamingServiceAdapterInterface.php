<?php

namespace App\Services;

interface StreamingServiceAdapterInterface
{
    const TITLE = 'Title';

    public function updateStreams();
}
