<?php

/*
 * IconCaptcha - Copyright 2023, Fabian Wennink (https://www.fabianwennink.nl)
 * Licensed under the MIT license: https://www.fabianwennink.nl/projects/IconCaptcha/license
 *
 * The above copyright notice and license shall be included in all copies or substantial portions of the software.
 */

namespace IconCaptcha\Session;

use IconCaptcha\Session\Exceptions\SessionDataParsingFailedException;
use JsonException;

class SessionData
{
    /**
     * @var array The positions of the icon on the generated image.
     */
    public array $icons = [];

    /**
     * @var int The icon ID of the correct answer/icon.
     */
    public int $correctId = 0;

    /**
     * @var string The name of the theme used by the captcha instance.
     */
    public string $mode = 'light';

    /**
     * @var bool If the captcha image has been requested yet.
     */
    public bool $requested = false;

    /**
     * @var bool If the captcha was completed (correct icon selected) or not.
     */
    public bool $completed = false;

    /**
     * @var int The (unix) timestamp, after which the captcha's session should be considered expired.
     */
    public int $expiresAt = 0;

    /**
     * Converts the session data into an array.
     */
    public function toArray(): array
    {
        return [
            'icons' => $this->icons,
            'correctId' => $this->correctId,
            'mode' => $this->mode,
            'requested' => $this->requested,
            'completed' => $this->completed,
            'expiresAt' => $this->expiresAt,
        ];
    }

    /**
     * Uses the values from the given array to set the session data.
     *
     * @throws SessionDataParsingFailedException If the JSON string could not be parsed.
     */
    public function fromArray(array $data): void
    {
        $missing = 0;

        $this->icons = $this->getArrayValue($data, 'icons', $missing);
        $this->correctId = $this->getArrayValue($data, 'correctId', $missing);
        $this->mode = $this->getArrayValue($data, 'mode', $missing);
        $this->requested = $this->getArrayValue($data, 'requested', $missing);
        $this->completed = $this->getArrayValue($data, 'completed', $missing);
        $this->expiresAt = $this->getArrayValue($data, 'expiresAt', $missing);

        // If any of the keys were missing, throw an exception.
        if ($missing > 0) {
            throw new SessionDataParsingFailedException();
        }
    }

    /**
     * Converts the session data into a JSON string.
     *
     * @throws SessionDataParsingFailedException If the JSON string could not be parsed.
     */
    public function toJson(): string
    {
        try {
            return json_encode($this->toArray(), JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new SessionDataParsingFailedException($exception);
        }
    }

    /**
     * Uses the given JSON string to fill the session data.
     *
     * @throws SessionDataParsingFailedException If the JSON string could not be parsed.
     */
    public function fromJson(string $json): void
    {
        try {
            $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
            $this->fromArray($data);
        } catch (JsonException $exception) {
            throw new SessionDataParsingFailedException($exception);
        }
    }

    /**
     * Converts the session data into a JSON string.
     *
     * @throws SessionDataParsingFailedException If the JSON string could not be parsed.
     */
    public function __toString(): string
    {
        return $this->toJson();
    }

    /**
     * Checks if a key exists in an array.
     *
     * @param array $arr The array to verify whether the key exists.
     * @param string $key The key to verify the existence of.
     * @param int $missing An increment counter, will be incremented by 1 if the key does not exist.
     * @return mixed|null The value of the key in the array, or NULL if the key does not exist.
     */
    private function getArrayValue(array $arr, string $key, int &$missing)
    {
        // Using both 'isset' and 'array_key_exists', as values might be NULL.
        if (isset($arr[$key]) || array_key_exists($key, $arr)) {
            return $arr[$key];
        }

        $missing++;

        return null;
    }
}
