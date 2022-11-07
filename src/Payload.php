<?php

namespace IconCaptcha;

class Payload
{
    /**
     * Tries to decode the given base64 and JSON encoded payload.
     * @param string $payload The request payload to be decoded.
     * @return mixed The decoded payload.
     */
    public static function decode($payload)
    {
        // Base64 decode the payload.
        $payload = base64_decode($payload);
        if ($payload === false) {
            return null;
        }

        // JSON decode the payload.
        return json_decode($payload, true);
    }

    /**
     * Encodes the given payload with base64 and JSON.
     * @param mixed $payload The payload to encode.
     * @return string The encoded payload.
     */
    public static function encode($payload)
    {
        return base64_encode(json_encode($payload));
    }
}
