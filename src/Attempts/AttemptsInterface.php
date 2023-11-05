<?php

/*
 * IconCaptcha - Copyright 2023, Fabian Wennink (https://www.fabianwennink.nl)
 * Licensed under the MIT license: https://www.fabianwennink.nl/projects/IconCaptcha/license
 *
 * The above copyright notice and license shall be included in all copies or substantial portions of the software.
 */

namespace IconCaptcha\Attempts;

interface AttemptsInterface
{
    /**
     * Returns the remaining timeout time in seconds.
     */
    public function getTimeoutRemainingTime(): int;

    /**
     * Increases the attempts history of a visitor by 1. In case the attempts
     * threshold was reached, a timeout will be set for the visitor.
     */
    public function increaseAttempts(): void;

    /**
     * Clears the attempts history and possible timeout of the visitor.
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
