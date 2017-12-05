<?php

namespace App\API\Exceptions;

class InvalidStreamTypeException extends \Exception
{
    public function __construct()
    {
        parent::__construct('Invalid \'streamType\' provided. StreamType can only be set to \'live\', \'vodcast\' or \'all\'.');
    }
}
