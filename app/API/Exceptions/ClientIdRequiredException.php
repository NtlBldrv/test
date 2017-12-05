<?php

namespace App\API\Exceptions;

class ClientIdRequiredException extends \Exception
{
    public function __construct()
    {
        parent::__construct('You must provide a \'client_id\' option.');
    }
}
