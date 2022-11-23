<?php

/**
 * IconCaptcha Plugin: v3.1.1
 * Copyright © 2022, Fabian Wennink (https://www.fabianwennink.nl)
 *
 * Licensed under the MIT license: https://www.fabianwennink.nl/projects/IconCaptcha/license
 */

namespace IconCaptcha\Session;

/**
 * @property array icons The positions of the icon on the generated image.
 * @property array iconIds List of used icon IDs.
 * @property int correctId The icon ID of the correct answer/icon.
 * @property string mode The name of the theme used by the captcha instance.
 * @property bool requested If the captcha image has been requested yet.
 * @property bool completed If the captcha was completed (correct icon selected) or not.
 * @property int attempts The number of times an incorrect answer was given.
 * @property int attemptsTimeout The (unix) timestamp, at which the timeout for entering too many incorrect guesses expires.
 * @property int expiresAt The (unix) timestamp, after which the captcha's session should be considered expire.
 */
class Session implements SessionInterface
{
    const SESSION_NAME = 'iconcaptcha';

    /**
     * @var string The captcha identifier.
     */
    protected $id;

    /**
     * @var array The session data.
     */
    private array $session = [];

    /**
     * Creates a new CaptchaSession object. Session data regarding the
     * captcha (given identifier) will be stored and can be retrieved when necessary.
     *
     * @param int $id The captcha identifier.
     */
    public function __construct(int $id = 0)
    {
        $this->id = $id;
        $this->load();
    }

    /**
     * @inheritDoc
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @inheritDoc
     */
    public function clear(): void
    {
        $this->session['icons'] = [];
        $this->session['iconIds'] = [];
        $this->session['correctId'] = 0;
        $this->session['requested'] = false;
        $this->session['completed'] = false;
        $this->session['attempts'] = 0;
        $this->session['attemptsTimeout'] = 0;
        $this->session['expiresAt'] = 0;
    }

    /**
     * @inheritDoc
     */
    public function destroy(): void
    {
        unset($_SESSION[self::SESSION_NAME][$this->id]);
    }

    /**
     * @inheritDoc
     */
    public function load(): void
    {
        // Make sure a session has been started.
        self::startSession();

        if (self::exists($this->id)) {
            $this->session = $_SESSION[self::SESSION_NAME][$this->id];
        } else {
            $this->session = [
                'icons' => [],
                'iconIds' => [],
                'correctId' => 0,
                'mode' => 'light',
                'requested' => false,
                'completed' => false,
                'attempts' => 0,
                'attemptsTimeout' => 0,
                'expiresAt' => 0,
            ];
        }
    }

    /**
     * @inheritDoc
     */
    public function save(): void
    {
        // Write the data to the session.
        $_SESSION[self::SESSION_NAME][$this->id] = $this->session;
    }

    /**
     * @inheritDoc
     */
    public static function exists(int $id): bool
    {
        self::startSession();

        return isset($_SESSION[self::SESSION_NAME][$id]);
    }

    /**
     * @inheritDoc
     */
    public function __get(string $key)
    {
        return $this->session[$key] ?? null;
    }

    /**
     * @inheritDoc
     */
    public function __set(string $key, $value): void
    {
        $this->session[$key] = $value;
    }

    /**
     * Attempts to start a session, if none has been started yet.
     * @return void
     */
    private static function startSession(): void
    {
        if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
            session_start();
        }
    }
}
