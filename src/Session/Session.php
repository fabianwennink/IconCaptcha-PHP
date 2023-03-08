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
abstract class Session implements SessionInterface
{
    /**
     * @var string The widget identifier.
     */
    protected string $widgetId;

    /**
     * @var ?string The challenge identifier.
     */
    protected ?string $challengeId;

    /**
     * @var SessionData The session data.
     */
    protected SessionData $data;

    /**
     * @var bool Whether the session data was loaded/created or not.
     */
    protected bool $dataLoaded = false;

    protected array $options;

    /**
     * Creates or loads a captcha session. If the widget and challenge identifiers are
     * given, an attempt will be made to load existing session data belonging to
     * the session identifiers. Otherwise, a new session will be created.
     *
     * @param array $options The captcha session options.
     * @param string $widgetId The widget unique identifier.
     * @param string|null $challengeId The challenge unique identifier.
     */
    public function __construct(array $options, string $widgetId, string $challengeId = null)
    {
        $this->options = $options;
        $this->widgetId = $widgetId;
        $this->challengeId = $challengeId;
        $this->data = new SessionData();
    }

    /**
     * Returns the identifier of the challenge.
     */
    public function getChallengeId(): ?string
    {
        return $this->challengeId;
    }

    /**
     * This will clear the set hashes, and reset the icon
     * request counter and last clicked icon.
     */
    public function clear(): void
    {
        $this->data->icons = [];
        $this->data->correctId = 0;
        $this->data->requested = false;
        $this->data->completed = false;
        $this->data->attempts = 0;
        $this->data->attemptsTimeout = 0;
        $this->data->expiresAt = 0;
    }

    /**
     * Returns whether the session has expired.
     */
    public function isExpired(): bool
    {
        return $this->data->expiresAt > 0 && $this->data->expiresAt < Utils::getTimeInMilliseconds();
    }

    /**
     * Retrieves data from the session based on the given property name.
     * @param string $key The name of the property in the session which should be retrieved.
     * @return mixed The data in the session, or NULL if the key does not exist.
     */
    public function __get(string $key)
    {
        return $this->data->{$key} ?? null;
    }

    /**
     * Set a value of the captcha session.
     * @param string $key The name of the property in the session which should be set.
     * @param mixed $value The value which should be stored.
     */
    public function __set(string $key, $value): void
    {
        $this->data->{$key} = $value;
    }

    public function hasSessionDataLoaded(): bool
    {
        return $this->dataLoaded;
    }

    /**
     * Generates a random UUID to be used as the challenge identifier.
     * @throws \Exception
     */
    protected function generateUniqueId(): string
    {
        $id = null;

        // Generate an identifier, making sure it is not already in use.
        while (empty($id) || $this->exists($id, $this->widgetId)) {
            $id = Utils::generateUUID();
        }

        return $id;
    }

    /**
     * Loads the captcha's session data based on the earlier set captcha identifier.
     */
    abstract protected function load(): void;

    /**
     * Deletes all expired sessions.
     */
    abstract protected function purgeExpired(): void;
}
