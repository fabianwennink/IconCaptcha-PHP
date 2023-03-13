<?php

namespace IconCaptcha\Session\Exceptions;

use InvalidArgumentException;

class SessionDatabaseOptionsException extends InvalidArgumentException
{
    /**
     * @inheritDoc
     */
    public function __construct($type)
    {
        $type = gettype($type);

        parent::__construct("Expected connection options to be either a PDO instance or an array containing database connection details, $type given.");
    }
}
