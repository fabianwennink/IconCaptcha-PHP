<?php

/**
 * IconCaptcha Plugin: v3.0.0
 * Copyright © 2021, Fabian Wennink (https://www.fabianwennink.nl)
 *
 * Licensed under the MIT license: http://www.opensource.org/licenses/mit-license.php
 */

namespace IconCaptcha;

class CaptchaSession
{
    const ICON_CAPTCHA = 'icon_captcha';
    const CAPTCHA_ICONS = 'icons';
    const CAPTCHA_ICON_POSITIONS = 'position';
    const CAPTCHA_COLOR_MODE = 'mode';
    const CAPTCHA_REQUESTED = 'requested';
    const CAPTCHA_COMPLETED = 'completed';

    /**
     * @var string The captcha identifier.
     */
    public $id;

    /**
     * @var array The correct icon position.
     */
    public $icons;

    /**
     * @var array The correct icon positions.
     */
    public $positions;

    /**
     * @var boolean If the icons image has been requested by the captcha.
     */
    public $requested;

    /**
     * @var string The captcha's icon color name.
     */
    public $mode;

    /**
     * @var bool If the captcha was completed (correct icon selected) or not.
     */
    public $completed;

    /**
     * Creates a new CaptchaSession object. Session data regarding the
     * captcha (given identifier) will be stored and can be retrieved when necessary.
     *
     * @param int $id The captcha identifier.
     */
    public function __construct($id = 0)
    {
        $this->id = $id;

        // Clear the session data.
        $this->clear();

        // Try to load the captcha data from the session, if any data exists.
        $this->load();
    }

    /**
     * This will clear the set hashes, and reset the icon
     * request counter and last clicked icon.
     */
    public function clear()
    {
        $this->icons = [];
        $this->positions = [];
        $this->requested = false;
        $this->completed = false;
    }

    /**
     * Destroys the captcha session.
     */
    public function destroy()
    {
        unset($_SESSION[self::ICON_CAPTCHA][$this->id]);
    }

    /**
     * Loads the captcha's session data based on the earlier set captcha identifier.
     */
    public function load()
    {
        if (self::exists($this->id)) {

            // Icons
            if (isset($_SESSION[self::ICON_CAPTCHA][$this->id][self::CAPTCHA_ICONS])) {
                $this->icons = $_SESSION[self::ICON_CAPTCHA][$this->id][self::CAPTCHA_ICONS];
            }

            // Position
            if (isset($_SESSION[self::ICON_CAPTCHA][$this->id][self::CAPTCHA_ICON_POSITIONS])) {
                $this->positions = $_SESSION[self::ICON_CAPTCHA][$this->id][self::CAPTCHA_ICON_POSITIONS];
            }

            // Mode
            if (isset($_SESSION[self::ICON_CAPTCHA][$this->id][self::CAPTCHA_COLOR_MODE])) {
                $this->mode = $_SESSION[self::ICON_CAPTCHA][$this->id][self::CAPTCHA_COLOR_MODE];
            }

            // Requested
            if (isset($_SESSION[self::ICON_CAPTCHA][$this->id][self::CAPTCHA_REQUESTED])) {
                $this->requested = $_SESSION[self::ICON_CAPTCHA][$this->id][self::CAPTCHA_REQUESTED];
            }

            // Completed
            if (isset($_SESSION[self::ICON_CAPTCHA][$this->id][self::CAPTCHA_COMPLETED])) {
                $this->completed = $_SESSION[self::ICON_CAPTCHA][$this->id][self::CAPTCHA_COMPLETED];
            }
        }
    }

    /**
     * Saves the current data to the session. The data will be stored in an array.
     */
    public function save()
    {
        $data = [
            self::CAPTCHA_ICONS => $this->icons,
            self::CAPTCHA_ICON_POSITIONS => $this->positions,
            self::CAPTCHA_COLOR_MODE => $this->mode,
            self::CAPTCHA_REQUESTED => $this->requested,
            self::CAPTCHA_COMPLETED => $this->completed
        ];

        // Write the data to the session.
        $_SESSION[self::ICON_CAPTCHA][$this->id] = $data;
    }

    /**
     * Checks if the given captcha identifier has session data stored.
     *
     * @param int $id The captcha identifier.
     *
     * @return boolean TRUE if any session data exists, FALSE if not.
     */
    public static function exists($id)
    {
        return isset($_SESSION[self::ICON_CAPTCHA][$id]);
    }
}