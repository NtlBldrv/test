<?php

namespace App\API\Exceptions;

class InvalidTypeException extends \Exception
{
    /**
     * @var string $name
     * @var string $expects
     * @var string $given
     */
    public function __construct($name, $expects, $given)
    {
        parent::__construct(sprintf('%s expects to be of type %s, %s given instead.', $name, $expects, $given));
    }
}
