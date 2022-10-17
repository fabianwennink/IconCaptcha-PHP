<?php

/**
 * IconCaptcha Plugin: v3.1.0
 * Copyright © 2022, Fabian Wennink (https://www.fabianwennink.nl)
 *
 * Licensed under the MIT license: https://www.fabianwennink.nl/projects/IconCaptcha/license
 */

namespace IconCaptcha;

class IconCaptchaSession implements IconCaptchaSessionInterface
{
    const SESSION_NAME = 'iconcaptcha';

    /**
     * @var string The captcha identifier.
     */
    protected $id;

    /**
     * @var array The session data.
     */
    private $session = [];

    /**
     * Creates a new CaptchaSession object. Session data regarding the
     * captcha (given identifier) will be stored and can be retrieved when necessary.
     *
     * @param int $id The captcha identifier.
     */
    public function __construct($id = 0)
    {
        $this->id = $id;
        $this->load();
    }

    /**
     * @inheritDoc
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
     * @inheritDoc
     */
    public function destroy()
    {
        unset($_SESSION[self::SESSION_NAME][$this->id]);
    }

    /**
     * @inheritDoc
     */
    public function load()
    {
        // Make sure a session has been started.
        IconCaptchaSession::startSession();

        if (self::exists($this->id)) {
            $this->session = $_SESSION[self::SESSION_NAME][$this->id];
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
     * @inheritDoc
     */
    public function save()
    {
        // Write the data to the session.
        $_SESSION[self::SESSION_NAME][$this->id] = $this->session;
    }

    /**
     * @inheritDoc
     */
    public static function exists($id)
    {
        self::startSession();

        return isset($_SESSION[self::SESSION_NAME][$id]);
    }

    /**
     * @inheritDoc
     */
    public function __get($key)
    {
        return isset($this->session[$key]) ? $this->session[$key] : null;
    }

    /**
     * @inheritDoc
     */
    public function __set($key, $value)
    {
        $this->session[$key] = $value;
    }

    /**
     * Attempts to start a session, if none has been started yet.
     * @return void
     */
    private static function startSession()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
}
