<?php

/*
 * IconCaptcha - Copyright 2023, Fabian Wennink (https://www.fabianwennink.nl)
 * Licensed under the MIT license: https://www.fabianwennink.nl/projects/IconCaptcha/license
 *
 * The above copyright notice and license shall be included in all copies or substantial portions of the software.
 */

namespace IconCaptcha\Challenge;

use IconCaptcha\Utils;

final class ValidationResult
{
    private bool $success;

    private ?string $errorCode;

    private int $timestamp;

    /**
     * Creates a new validation result instance.
     *
     * @param bool $success
     * @param string|null $errorCode
     */
    public function __construct(bool $success, string $errorCode = null)
    {
        $this->success = $success;
        $this->errorCode = $errorCode;
        $this->timestamp = Utils::getCurrentTimeInMilliseconds();
    }

    /**
     * Returns whether the captcha was validated successfully or not.
     */
    public function success(): bool
    {
        return $this->success;
    }

    /**
     * Returns the error code of the reason as to why the verification failed.
     * Will only be set in case the response of {@see success} is FALSE.
     */
    public function getErrorCode(): ?string
    {
        return $this->errorCode;
    }

    /**
     * Returns the time of verification, as a timestamp, in milliseconds.
     */
    public function getTimestamp(): int
    {
        return $this->timestamp;
    }

    public function __toString(): string
    {
        return json_encode(array_values([
            'success' => $this->success,
            'error_code' => $this->errorCode,
            'timestamp' => $this->timestamp,
        ]), JSON_THROW_ON_ERROR);
    }
}
