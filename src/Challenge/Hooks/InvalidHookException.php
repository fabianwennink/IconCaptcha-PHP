<?php

namespace IconCaptcha\Challenge\Hooks;

use Exception;
use Throwable;

class InvalidHookException extends Exception
{
    /**
     * Exception class thrown when a custom hook is invalid, which can indicate a missing class or incorrectly implemented interface.
     *
     * @param string $path The path of the non-existing file.
     * @inheritDoc
     */
    public function __construct(string $hook, string $message = null, int $code = 0, Throwable $previous = null)
    {
        if ($message === null) {
            $message = "Hook \"$hook\" is invalid. A hook must be a class implementing the necessary interface.";
        }

        parent::__construct($message, $code, $previous);
    }
}

