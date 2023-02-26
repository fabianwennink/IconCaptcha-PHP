<?php

namespace IconCaptcha\Session;

interface SessionInterface
{
    /**
     * Saves the current data to the session. The data will be stored in an array.
     */
    public function save(): void;

    /**
     * Destroys the captcha session.
     */
    public function destroy(): void;

    /**
     * Checks if the given challenge and widget identifier combination has session data stored.
     * @param string $challengeId The challenge identifier.
     * @param string $widgetId The widget identifier.
     * @return boolean TRUE if any session data exists, FALSE if not.
     */
    public function exists(string $challengeId, string $widgetId): bool;
}
