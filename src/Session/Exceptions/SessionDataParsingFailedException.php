<?php

namespace IconCaptcha\Session\Exceptions;

use Exception;
use IconCaptcha\Session\SessionData;
use Throwable;

class SessionDataParsingFailedException extends Exception
{
    /**
     * Exception thrown if the Session data can't be parsed. This indicates that the object used to set the internal
     * data of a {@see SessionData} instance with is formatted incorrectly, or is missing one or more required fields.
     * @inheritDoc
     */
    public function __construct(Throwable $previous = null)
    {
        $message = 'Failed to parse the data of a SessionData instance.';

        if ($previous !== null) {
            $message .= ' Exception: ' . $previous->getMessage();
        }

        parent::__construct($message, 0, $previous);
    }
}
