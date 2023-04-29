<?php

namespace IconCaptcha;

use Closure;
use Exception;
use http\Exception\InvalidArgumentException;
use IconCaptcha\Session\Session;
use IconCaptcha\Session\SessionFactory;

class Utils
{
    /**
     * Returns the current Unix timestamp in milliseconds.
     */
    public static function getCurrentTimeInMilliseconds(): int
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
        $data = random_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

    /**
     * Checks if a string is in the UUID v4 format.
     * @param string $string The string to check.
     */
    public static function isUUIDv4(string $string): bool
    {
        return preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', $string) === 1;
    }

    /**
     * Returns the IP address of the visitor.
     * @param string|Closure $option A function which returns the IP address, or an already known IP address.
     * @return string
     */
    public static function getIpAddress($option): ?string
    {
        if(is_string($option)) {
            return $option;
        }

        if(is_callable($option)) {
            return $option();
        }

        throw new InvalidArgumentException("The 'ipAddress' options is invalid.");
    }

    /**
     * Creates a new instance of a session.
     * @param mixed $storage The storage container.
     * @param array $sessionOptions The captcha session options.
     * @param string $ipAddress The IP address of the visitor.
     * @param string $widgetId The widget identifier of the captcha.
     * @param string|null $challengeId The challenge identifier of the captcha.
     */
    public static function createSession($storage, array $sessionOptions, string $ipAddress, string $widgetId, string $challengeId = null): Session
    {
        return SessionFactory::create(
            $storage,
            $sessionOptions['driver'],
            $sessionOptions['options'] ?? [],
            $ipAddress,
            $widgetId,
            $challengeId
        );
    }
}
