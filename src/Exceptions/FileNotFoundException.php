<?php

namespace IconCaptcha\Exceptions;

use Exception;
use Throwable;

class FileNotFoundException extends Exception
{
    /**
     * @inheritDoc
     */
    public function __construct(string $path, string $message = null, int $code = 0, Throwable $previous = null)
    {
        if ($message === null) {
            $message = "File \"$path\" could not be found.";
        }

        parent::__construct($message, $code, $previous);
    }
}
