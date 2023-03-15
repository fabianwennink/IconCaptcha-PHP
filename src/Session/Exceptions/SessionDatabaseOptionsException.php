<?php

namespace IconCaptcha\Session\Exceptions;

use InvalidArgumentException;

class SessionDatabaseOptionsException extends InvalidArgumentException
{
    /**
     * Exception thrown if the connection options for a PDO-based Session driver is invalid.
     * @param mixed $options The options object, which is supposed to contain the database connection details.
     */
    public function __construct($options)
    {
        $options = gettype($options);

        parent::__construct("Expected connection options to be either a PDO instance or an array containing database connection details, $options given.");
    }
}
