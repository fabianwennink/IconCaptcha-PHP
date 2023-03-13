<?php

namespace IconCaptcha;

use JsonException;

class Payload
{
    /**
     * Tries to decode the given base64 and JSON encoded payload.
     * @param string $payload The request payload to be decoded.
     * @return mixed The decoded payload.
     * @throws JsonException
     */
    public static function decode(string $payload)
    {
        // Base64 decode the payload.
        $payload = base64_decode($payload);
        if ($payload === false) {
            return null;
        }

        // JSON decode the payload.
        return json_decode($payload, true, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * Encodes the given payload with base64 and JSON.
     * Note: All NULL values will be filtered.
     * @param mixed $payload The payload to encode.
     * @return string The encoded payload.
     * @throws JsonException
     */
    public static function encode($payload): string
    {
        return base64_encode(json_encode(
            array_filter($payload) + ['timestamp' => Utils::getTimeInMilliseconds()],
            JSON_THROW_ON_ERROR
        ));
    }
}
