<?php

namespace IconCaptcha\Challenge;

use IconCaptcha\Utils;

final class ValidationResult
{
    private bool $success;

    private ?int $errorCode;

    private ?string $errorMessage;

    private int $timestamp;

    /**
     * @param bool $success
     * @param int|null $errorCode
     * @param string|null $errorMessage
     */
    public function __construct(bool $success, int $errorCode = null, string $errorMessage = null)
    {
        $this->success = $success;
        $this->errorCode = $errorCode;
        $this->errorMessage = $errorMessage;
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
     * @return int|null The error code.
     */
    public function getErrorCode(): ?int
    {
        return $this->errorCode;
    }

    /**
     * Returns the error message of the reason as to why the verification failed.
     * Will only be set in case the response of {@see success} is FALSE.
     * @return string|null The error message.
     */
    public function getErrorMessage(): ?string
    {
        return $this->errorMessage;
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
        return json_encode([
            'success' => $this->success,
            'error' => [
                'code' => $this->errorCode,
                'message' => $this->errorMessage,
            ],
            'timestamp' => $this->timestamp,
        ]);
    }
}
