<?php

namespace IconCaptcha\Session\Exceptions;

use Exception;
use Throwable;

class SessionDataParsingFailedException extends Exception
{
    /**
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
