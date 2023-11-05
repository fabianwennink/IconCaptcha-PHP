<?php

/*
 * IconCaptcha - Copyright 2023, Fabian Wennink (https://www.fabianwennink.nl)
 * Licensed under the MIT license: https://www.fabianwennink.nl/projects/IconCaptcha/license
 *
 * The above copyright notice and license shall be included in all copies or substantial portions of the software.
 */

namespace IconCaptcha\Storage\Database\Exceptions;

use InvalidArgumentException;

class DatabaseOptionsException extends InvalidArgumentException
{
    /**
     * Exception thrown if the connection options for a PDO-based driver is invalid.
     *
     * @param mixed $options The options object, which is supposed to contain the database connection details.
     */
    public function __construct($options)
    {
        $options = gettype($options);

        parent::__construct("Expected connection options to be either a PDO instance or an array containing database connection details, $options given.");
    }
}
