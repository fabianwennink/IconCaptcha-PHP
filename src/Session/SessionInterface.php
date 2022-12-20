<?php

namespace IconCaptcha\Session;

interface SessionInterface
{
    /**
     * Returns the identifier of the challenge.
     * @return string
     */
    public function getChallengeId(): string;

    /**
     * Loads the captcha's session data based on the earlier set captcha identifier.
     */
    public function load(): void;

    /**
     * Saves the current data to the session. The data will be stored in an array.
     */
    public function save(): void;

    /**
     * This will clear the set hashes, and reset the icon
     * request counter and last clicked icon.
     */
    public function clear(): void;

    /**
     * Destroys the captcha session.
     */
    public function destroy(): void;

    /**
     * Returns whether the session has expired.
     * @return bool TRUE if it's expired, FALSE if it's not.
     */
    public function isExpired(): bool;

    /**
     * Deletes all expired sessions.
     * @return void
     */
    public static function purgeExpired(): void;

    /**
     * Checks if the given challenge and widget identifier combination has session data stored.
     * @param string $challengeId The challenge identifier.
     * @param string $widgetId The widget identifier.
     * @return boolean TRUE if any session data exists, FALSE if not.
     */
    public static function exists(string $challengeId, string $widgetId): bool;

    /**
     * Retrieves data from the session based on the given property name.
     * @param string $key The name of the property in the session which should be retrieved.
     * @return mixed The data in the session, or NULL if the key does not exist.
     */
    public function __get(string $key);

    /**
     * Set a value of the captcha session.
     * @param string $key The name of the property in the session which should be set.
     * @param mixed $value The value which should be stored.
     */
    public function __set(string $key, $value);
}
