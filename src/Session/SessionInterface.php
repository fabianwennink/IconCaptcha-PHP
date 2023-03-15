<?php

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
     * @param string $challengeId The challenge identifier.
     * @param string $widgetId The widget identifier.
     */
    public function exists(string $challengeId, string $widgetId): bool;
}
