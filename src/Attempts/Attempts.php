<?php

namespace IconCaptcha\Attempts;

use IconCaptcha\Utils;

abstract class Attempts implements AttemptsInterface
{
    protected array $options;

    public function __construct(array $options)
    {
        $this->options = $options;
    }

    /**
     * @inheritDoc
     */
    public function isEnabled(): bool
    {
        return $this->options['enabled'] && $this->options['timeout'] > 0;
    }

    /**
     * @inheritDoc
     */
    public function isTimeoutActive(): bool
    {
        $currentTimestamp = Utils::getCurrentTimeInMilliseconds();

        // Read the active timeout timestamp from session. Will be NULL if no timeout is active.
        $storedTimeoutTimestamp = $this->getActiveTimeoutTimestamp();

        return $storedTimeoutTimestamp !== null && $storedTimeoutTimestamp >= $currentTimestamp;
    }

    /**
     * @inheritDoc
     */
    public function getTimeoutRemainingTime(): int
    {
        $currentTimestamp = Utils::getCurrentTimeInMilliseconds();

        // Read the active timeout timestamp from session. Will be NULL if no timeout is active.
        $storedTimeoutTimestamp = $this->getActiveTimeoutTimestamp();

        // If there is a timeout active, calculate the remaining time.
        if($storedTimeoutTimestamp !== null) {
            $remainingTime = $storedTimeoutTimestamp - $currentTimestamp;
            if($remainingTime > 0) {
                return $remainingTime;
            }
        }

        return 0;
    }

    /**
     * Generates a new validity timestamp in milliseconds, based on the configuration and current timestamp.
     */
    protected function getNewValidityTimestamp(): int
    {
        return ($this->options['valid'] * 1000) + Utils::getCurrentTimeInMilliseconds();
    }

    /**
     * Returns whether the stored attempts are still valid. If the validity timestamp has
     * expired, the attempts which were made thus far should be seen as invalid.
     */
    protected function isAttemptsDataStillValid(): bool
    {
        $storedValidityTimestamp = $this->getAttemptsValidityTimestamp();
        return $storedValidityTimestamp !== null && $storedValidityTimestamp >= Utils::getCurrentTimeInMilliseconds();
    }

    /**
     * Attempts to retrieve the current active timeout timestamp from storage.
     * @return int|null The timeout expiration timestamp, if set, otherwise NULL.
     */
    abstract protected function getActiveTimeoutTimestamp(): ?int;

    /**
     * Attempts to retrieve the current attempts count made by the visitor from storage.
     * @return int|null The amount of attempts made by the visitor, if set, otherwise NULL.
     */
    abstract protected function getCurrentAttemptsCount(): ?int;

    /**
     * Attempts to retrieve the validity timestamp from storage.
     * @return int|null The timestamp, if set, otherwise NULL.
     */
    abstract protected function getAttemptsValidityTimestamp(): ?int;

    /**
     * Issues a new temporary timeout for a visitor.
     * @param int $currentTimestamp The current timestamp, in milliseconds.
     * @return bool Whether the timeout was successfully issued.
     */
    abstract protected function issueTimeout(int $currentTimestamp): bool;
}
