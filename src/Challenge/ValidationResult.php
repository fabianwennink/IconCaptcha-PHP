<?php

namespace IconCaptcha\Challenge;

use IconCaptcha\Utils;

final class ValidationResult
{
    private bool $success;

    private ?string $errorCode;

    private int $timestamp;

    /**
     * @param bool $success
     * @param string|null $errorCode
     */
    public function __construct(bool $success, string $errorCode = null)
    {
        $this->success = $success;
        $this->errorCode = $errorCode;
        $this->timestamp = Utils::getTimeInMilliseconds();
    }

    /**
     * Returns whether the captcha was validated successfully or not.
     * @return bool TRUE if validated, FALSE if validation failed.
     */
    public function success(): bool
    {
        return $this->success;
    }

    /**
     * Returns the error code of the reason as to why the verification failed.
     * Will only be set in case the response of {@see success} is FALSE.
     * @return string|null The error code.
     */
    public function getErrorCode(): ?string
    {
        return $this->errorCode;
    }

    /**
     * Returns the time of verification, as a timestamp, in milliseconds.
     * @return int The verification timestamp.
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
        ]));
    }
}
