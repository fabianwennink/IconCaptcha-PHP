<?php

/*
 * IconCaptcha - Copyright 2023, Fabian Wennink (https://www.fabianwennink.nl)
 * Licensed under the MIT license: https://www.fabianwennink.nl/projects/IconCaptcha/license
 *
 * The above copyright notice and license shall be included in all copies or substantial portions of the software.
 */

namespace IconCaptcha\Session;

interface SessionInterface
{
    /**
     * Saves the current data to the session storage.
     */
    public function save(): void;

    /**
     * Destroys the session data.
     */
    public function destroy(): void;

    /**
     * Checks if the given challenge and widget identifier combination has session data stored.
     *
     * @param string $challengeId The challenge identifier.
     * @param string $widgetId The widget identifier.
     */
    public function exists(string $challengeId, string $widgetId): bool;
}
