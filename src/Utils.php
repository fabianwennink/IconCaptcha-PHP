<?php

namespace IconCaptcha;

use Exception;
use IconCaptcha\Session\Session;

class Utils
{
    /**
     * Returns the current Unix timestamp in milliseconds.
     */
    public static function getTimeInMilliseconds(): int
    {
        return round(microtime(true) * 1000);
    }

    /**
     * Generates a random UUID.
     * @return string The generated UUID.
     * @throws Exception
     */
    public static function generateUUID(): string
    {
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex(random_bytes(16)), 4));
    }

    /**
     * Creates a new instance of a session.
     * @param array $options The captcha options.
     * @param string $widgetId The widget identifier of the captcha.
     * @param string|null $challengeId The challenge identifier of the captcha.
     */
    public static function createSession(array $options, string $widgetId, string $challengeId = null): Session
    {
        return new $options['session']['driver'](
            $options['session']['options'] ?? [],
            $widgetId, $challengeId
        );
    }
}
