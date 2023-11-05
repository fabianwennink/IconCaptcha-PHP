<?php

/*
 * IconCaptcha - Copyright 2023, Fabian Wennink (https://www.fabianwennink.nl)
 * Licensed under the MIT license: https://www.fabianwennink.nl/projects/IconCaptcha/license
 *
 * The above copyright notice and license shall be included in all copies or substantial portions of the software.
 */

namespace IconCaptcha;

use JsonException;

class Payload
{
    /**
     * Tries to decode the given base64 and JSON encoded payload.
     *
     * @throws JsonException If the payload contains invalid JSON.
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
     *
     * @throws JsonException If the payload contains invalid data which fails to be encoded.
     */
    public static function encode(array $payload): string
    {
        return base64_encode(json_encode(
            array_filter($payload) + ['timestamp' => Utils::getCurrentTimeInMilliseconds()],
            JSON_THROW_ON_ERROR
        ));
    }
}
