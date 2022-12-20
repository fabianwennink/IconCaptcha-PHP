<?php

namespace IconCaptcha;

class Utils
{
    /**
     * Returns the current Unix timestamp in milliseconds.
     * @return int The timestamp.
     */
    public static function getTimeInMilliseconds(): int
    {
        return round(microtime(true) * 1000);
    }

    /**
     * Generates a random UUID.
     * @return string The generated UUID.
     * @throws \Exception
     */
    public static function generateUUID(): string
    {
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex(random_bytes(16)), 4));
    }
}
