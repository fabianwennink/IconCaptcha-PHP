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
}
