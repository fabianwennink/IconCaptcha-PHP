<?php

/*
 * IconCaptcha - Copyright 2023, Fabian Wennink (https://www.fabianwennink.nl)
 * Licensed under the MIT license: https://www.fabianwennink.nl/projects/IconCaptcha/license
 *
 * The above copyright notice and license shall be included in all copies or substantial portions of the software.
 */

namespace IconCaptcha\Attempts;

use IconCaptcha\Utils;

abstract class Attempts implements AttemptsInterface
{
    /**
     * @var array The captcha attempts/timeout options.
     */
    protected array $options;

    /**
     * Creates a new attempts and timeout manager instance.
     *
     * @param array $options The captcha attempts/timeout options.
     */
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
        // Read the active timeout timestamp from session. Will be NULL if no timeout is active.
        $storedTimeoutTimestamp = $this->getActiveTimeoutTimestamp();

        return $storedTimeoutTimestamp !== null && $storedTimeoutTimestamp >= Utils::getCurrentTimeInSeconds();
    }

    /**
     * @inheritDoc
     */
    public function getTimeoutRemainingTime(): int
    {
        // Read the active timeout timestamp from session. Will be NULL if no timeout is active.
        $storedTimeoutTimestamp = $this->getActiveTimeoutTimestamp();

        // If there is a timeout active, calculate the remaining time.
        if ($storedTimeoutTimestamp !== null) {
            $remainingTime = $storedTimeoutTimestamp - Utils::getCurrentTimeInSeconds();
            if ($remainingTime > 0) {
                return $remainingTime;
            }
        }

        return 0;
    }

    /**
     * Generates a new validity timestamp (seconds) based on the configuration and current timestamp.
     *
     * @return int The validity timestamp.
     */
    protected function getNewValidityTimestamp(): int
    {
        return $this->options['valid'] + Utils::getCurrentTimeInSeconds();
    }

    /**
     * Returns whether the stored attempts are still valid. If the validity timestamp has
     * expired, the attempts which were made thus far should be seen as invalid.
     */
    protected function isAttemptsDataStillValid(): bool
    {
        $storedValidityTimestamp = $this->getAttemptsValidityTimestamp();
        return $storedValidityTimestamp !== null && $storedValidityTimestamp >= Utils::getCurrentTimeInSeconds();
    }

    /**
     * Attempts to retrieve the current active timeout timestamp from storage.
     *
     * @return int|null The timeout expiration timestamp, if set, otherwise NULL.
     */
    abstract protected function getActiveTimeoutTimestamp(): ?int;

    /**
     * Attempts to retrieve the current attempts count made by the visitor from storage.
     *
     * @return int|null The amount of attempts made by the visitor, if set, otherwise NULL.
     */
    abstract protected function getCurrentAttemptsCount(): ?int;

    /**
     * Attempts to retrieve the validity timestamp from storage.
     *
     * @return int|null The timestamp, if set, otherwise NULL.
     */
    abstract protected function getAttemptsValidityTimestamp(): ?int;

    /**
     * Issues a new temporary timeout for the visitor.
     *
     * @return bool Whether the timeout was successfully issued.
     */
    abstract protected function issueTimeout(): bool;
}
