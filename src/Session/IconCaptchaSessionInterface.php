<?php

namespace IconCaptcha\Session;

interface IconCaptchaSessionInterface
{
    /**
     * This will clear the set hashes, and reset the icon
     * request counter and last clicked icon.
     */
    public function clear();

    /**
     * Destroys the captcha session.
     */
    public function destroy();

    /**
     * Loads the captcha's session data based on the earlier set captcha identifier.
     */
    public function load();

    /**
     * Saves the current data to the session. The data will be stored in an array.
     */
    public function save();

    /**
     * Checks if the given captcha identifier has session data stored.
     *
     * @param int $id The captcha identifier.
     *
     * @return boolean TRUE if any session data exists, FALSE if not.
     */
    public static function exists($id);

    /**
     * Retrieves data from the session based on the given property name.
     * @param string $key The name of the property in the session which should be retrieved.
     * @return mixed The data in the session, or NULL if the key does not exist.
     */
    public function __get($key);

    /**
     * Set a value of the captcha session.
     * @param string $key The name of the property in the session which should be set.
     * @param mixed $value The value which should be stored.
     */
    public function __set($key, $value);
}
