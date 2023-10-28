<?php

/*
 * IconCaptcha - Copyright 2023, Fabian Wennink (https://www.fabianwennink.nl)
 * Licensed under the MIT license: https://www.fabianwennink.nl/projects/IconCaptcha/license
 *
 * The above copyright notice and license shall be included in all copies or substantial portions of the software.
 */

namespace IconCaptcha\Session\Drivers;

use IconCaptcha\Session\Session;
use IconCaptcha\Storage\Session\SessionStorageInterface;
use IconCaptcha\Utils;

class ServerSession extends Session
{
    /**
     * @var string The key used to store the challenges at in the session.
     */
    private string $sessionKey = 'challenges';

    /**
     * @var SessionStorageInterface The session storage container.
     */
    private SessionStorageInterface $storage;

    /**
     * Initializes a new server session instance.
     *
     * @param SessionStorageInterface $storage The session storage container.
     * @param array $options The captcha session options.
     * @param string $ipAddress The IP address of the visitor.
     * @param string $widgetId The captcha widget identifier.
     * @param string|null $challengeId The captcha challenge identifier.
     */
    public function __construct(SessionStorageInterface $storage, array $options, string $ipAddress, string $widgetId, string $challengeId = null)
    {
        parent::__construct($options, $ipAddress, $widgetId, $challengeId);

        $this->storage = $storage;

        // Purge any expired attempts records, if enabled.
        if (!isset($this->options['options']['purging']) || $this->options['options']['purging']) {
            $this->purgeExpired();
        }

        $this->load();
    }

    /**
     * @inheritDoc
     */
    protected function load(): void
    {
        if ($this->exists($this->challengeId ?? '', $this->widgetId)) {
            $this->puzzle->fromArray(
                $this->storage->read("$this->sessionKey.$this->widgetId:$this->challengeId")
            );
        } else {
            $this->challengeId = $this->challengeId ?? $this->generateUniqueId();
        }

        $this->dataLoaded = true;
    }

    /**
     * @inheritDoc
     */
    public function save(): void
    {
        // Write the data to the session.
        $this->storage->write("$this->sessionKey.$this->widgetId:$this->challengeId", $this->puzzle->toArray());
    }

    /**
     * @inheritDoc
     */
    public function destroy(): void
    {
        $this->storage->remove("$this->sessionKey.$this->widgetId:$this->challengeId");
    }

    /**
     * @inheritDoc
     */
    protected function purgeExpired(): void
    {
        // If the session is not set yet, do nothing.
        if (!$this->storage->exists($this->sessionKey)) {
            return;
        }

        // Check all existing sessions, deleting the ones which are expired.
        foreach ($this->storage->read($this->sessionKey) as $id => $session) {
            if ($session['expiresAt'] > 0 && $session['expiresAt'] < Utils::getCurrentTimeInMilliseconds()) {
                $this->storage->remove("$this->sessionKey.$id");
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function exists(string $challengeId, string $widgetId): bool
    {
        return !empty($challengeId) && !empty($widgetId)
            && $this->storage->exists("$this->sessionKey.$widgetId:$challengeId");
    }
}
