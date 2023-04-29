<?php

namespace IconCaptcha\Attempts;

interface AttemptsInterface
{
    /**
     * Returns the remaining timeout time in milliseconds.
     */
    public function getTimeoutRemainingTime(): int;

    /**
     * Increases the attempts history of a visitor by 1. In case the attempts
     * threshold was reached, a timeout will be set for the visitor.
     * @param int $timestamp
     */
    public function increaseAttempts(int $timestamp): void;

    /**
     * Clears the attempts history and possible timeout of a visitor.
     */
    public function clearAttempts(): void;

    /**
     * Returns whether a timeout is active for the visitor.
     */
    public function isTimeoutActive(): bool;

    /**
     * Returns whether the 'attempts/timeout' feature is enabled.
     */
    public function isEnabled(): bool;
}
