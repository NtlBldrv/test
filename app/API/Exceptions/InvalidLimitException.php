<?php

namespace App\API\Exceptions;

class InvalidLimitException extends \Exception
{
    public function __construct()
    {
        parent::__construct('Invalid \'limit\' provided. Limit must be a numeric value greater than 0.');
    }
}
