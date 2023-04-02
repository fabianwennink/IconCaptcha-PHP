<?php

/**
 * IconCaptcha Plugin: v3.1.2
 * Copyright © 2023, Fabian Wennink (https://www.fabianwennink.nl)
 *
 * Licensed under the MIT license: https://www.fabianwennink.nl/projects/IconCaptcha/license
 */

namespace IconCaptcha;

class CaptchaSession
{
    /**
     * @var string The captcha identifier.
     */
    protected $id;

    /**
     * @var string The name/key of the session.
     */
    protected $key;

    /**
     * @var array The session data.
     */
    protected $session = [];

    /**
     * Creates a new CaptchaSession object. Session data regarding the
     * captcha (given identifier) will be stored and can be retrieved when necessary.
     *
     * @param string $key The name of the session key.
     * @param int $id The captcha identifier.
     */
    public function __construct($key, $id = 0)
    {
        $this->id = $id;
        $this->key = $key;

        // Try to load the captcha data from the session, if any data exists.
        $this->load();
    }

    /**
     * This will clear the set hashes, and reset the icon
     * request counter and last clicked icon.
     */
    public function clear()
    {
        $this->session['icons'] = [];
        $this->session['iconIds'] = [];
        $this->session['correctId'] = 0;
        $this->session['requested'] = false;
        $this->session['completed'] = false;
        $this->session['attempts'] = 0;
    }

    /**
     * Destroys the captcha session.
     */
    public function destroy()
    {
        unset($_SESSION[$this->key][$this->id]);
    }

    /**
     * Loads the captcha's session data based on the earlier set captcha identifier.
     */
    public function load()
    {
        if (self::exists($this->key, $this->id)) {
            $this->session = $_SESSION[$this->key][$this->id];
        } else {
            $this->session = [
                'icons' => [], // The positions of the icon on the generated image.
                'iconIds' => [], // List of used icon IDs.
                'correctId' => 0, // The icon ID of the correct answer/icon.
                'mode' => 'light', // The name of the theme used by the captcha instance.
                'requested' => false, // If the captcha image has been requested yet.
                'completed' => false, // If the captcha was completed (correct icon selected) or not.
                'attempts' => 0, // The number of times an incorrect answer was given.
            ];
        }
    }

    /**
     * Saves the current data to the session. The data will be stored in an array.
     */
    public function save()
    {
        // Write the data to the session.
        $_SESSION[$this->key][$this->id] = $this->session;
    }

    /**
     * Checks if the given captcha identifier has session data stored.
     *
     * @param string $key The name of the session key.
     * @param int $id The captcha identifier.
     *
     * @return boolean TRUE if any session data exists, FALSE if not.
     */
    public static function exists($key, $id)
    {
        return isset($_SESSION[$key][$id]);
    }

    /**
     * Retrieves data from the session based on the given property name.
     * @param string $key The name of the property in the session which should be retrieved.
     * @return mixed The data in the session, or NULL if the key does not exist.
     */
    public function __get($key)
    {
        return isset($this->session[$key]) ? $this->session[$key] : null;
    }

    /**
     * Set a value of the captcha session.
     * @param string $key The name of the property in the session which should be set.
     * @param mixed $value The value which should be stored.
     */
    public function __set($key, $value)
    {
        $this->session[$key] = $value;
    }
}
