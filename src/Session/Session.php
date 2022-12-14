<?php

/**
 * IconCaptcha Plugin: v3.1.1
 * Copyright © 2022, Fabian Wennink (https://www.fabianwennink.nl)
 *
 * Licensed under the MIT license: https://www.fabianwennink.nl/projects/IconCaptcha/license
 */

namespace IconCaptcha\Session;

use IconCaptcha\Utils;

/**
 * @property array icons The positions of the icon on the generated image.
 * @property int correctId The icon ID of the correct answer/icon.
 * @property string mode The name of the theme used by the captcha instance.
 * @property bool requested If the captcha image has been requested yet.
 * @property bool completed If the captcha was completed (correct icon selected) or not.
 * @property int attempts The number of times an incorrect answer was given.
 * @property int attemptsTimeout The (unix) timestamp, at which the timeout for entering too many incorrect guesses expires.
 * @property int expiresAt The (unix) timestamp, after which the captcha's session should be considered expired.
 */
class Session implements SessionInterface
{
    private const SESSION_NAME = 'iconcaptcha';

    /**
     * @var string The challenge identifier.
     */
    protected string $challengeId;

    /**
     * @var string The widget identifier.
     */
    protected string $widgetId;

    /**
     * @var array The session data.
     */
    private array $data = [];

    /**
     * Creates a new CaptchaSession object. Session data regarding the
     * captcha (given identifier) will be stored and can be retrieved when necessary.
     *
     * @param string $widgetId The widget identifier.
     * @param string|null $challengeId The challenge identifier.
     */
    public function __construct(string $widgetId, string $challengeId = null)
    {
        $this->widgetId = $widgetId;
        $this->challengeId = $challengeId ?? $this->generateUniqueId();
        $this->load();
    }

    /**
     * @inheritDoc
     */
    public function getChallengeId(): string
    {
        return $this->challengeId;
    }

    /**
     * @inheritDoc
     */
    public function clear(): void
    {
        $this->data['icons'] = [];
        $this->data['correctId'] = 0;
        $this->data['requested'] = false;
        $this->data['completed'] = false;
        $this->data['attempts'] = 0;
        $this->data['attemptsTimeout'] = 0;
        $this->data['expiresAt'] = 0;
    }

    /**
     * @inheritDoc
     */
    public function destroy(): void
    {
        unset($_SESSION[self::SESSION_NAME][$this->challengeId]);
    }

    /**
     * @inheritDoc
     */
    public function load(): void
    {
        // Make sure a session has been started.
        self::startSession();

        if (self::exists($this->challengeId, $this->widgetId)) {
            $this->data = $_SESSION[self::SESSION_NAME][$this->challengeId];
        } else {
            $this->data = [
                'widget' => $this->widgetId,
                'icons' => [],
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
        $_SESSION[self::SESSION_NAME][$this->challengeId] = $this->data;
    }

    /**
     * @inheritDoc
     */
    public function isExpired(): bool
    {
        return $this->data['expiresAt'] > 0 && $this->data['expiresAt'] < Utils::getTimeInMilliseconds();
    }

    /**
     * @inheritDoc
     */
    public static function exists(string $challengeId, string $widgetId): bool
    {
        self::startSession();

        return isset($_SESSION[self::SESSION_NAME][$challengeId]) && $_SESSION[self::SESSION_NAME][$challengeId]['widget'] === $widgetId;
    }

    /**
     * @inheritDoc
     */
    public function __get(string $key)
    {
        return $this->data[$key] ?? null;
    }

    /**
     * @inheritDoc
     */
    public function __set(string $key, $value): void
    {
        $this->data[$key] = $value;
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

    /**
     * Generates a random UUID to be used as the challenge identifier.
     * @return string The generated UUID.
     * @throws \Exception
     */
    private function generateUniqueId(): string
    {
        $id = null;

        // Generate an identifier, making sure it is not already in use.
        while (empty($id) || static::exists($id, $this->widgetId)) {
            $id = vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex(random_bytes(16)), 4));
        }

        return $id;
    }
}
