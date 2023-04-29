<?php

namespace IconCaptcha;

use JsonException;

class Payload
{
    /**
     * Tries to decode the given base64 and JSON encoded payload.
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
     * @throws JsonException
     */
    public static function encode(array $payload): string
    {
        return base64_encode(json_encode(
            array_filter($payload) + ['timestamp' => Utils::getCurrentTimeInMilliseconds()],
            JSON_THROW_ON_ERROR
        ));
    }
}
