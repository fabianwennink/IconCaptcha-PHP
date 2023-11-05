<?php

/*
 * IconCaptcha - Copyright 2023, Fabian Wennink (https://www.fabianwennink.nl)
 * Licensed under the MIT license: https://www.fabianwennink.nl/projects/IconCaptcha/license
 *
 * The above copyright notice and license shall be included in all copies or substantial portions of the software.
 */

namespace IconCaptcha\Session\Drivers;

use IconCaptcha\Session\Session;
use IconCaptcha\Storage\KeyValueStorageInterface;
use IconCaptcha\Utils;

class KeyValueSession extends Session
{
    /**
     * @var string The key used to store the challenges at in the storage container.
     */
    private string $key = 'challenges';

    /**
     * @var KeyValueStorageInterface The storage container.
     */
    private KeyValueStorageInterface $storage;

    /**
     * Creates a new server session instance.
     *
     * @param KeyValueStorageInterface $storage The storage container.
     * @param array $options The captcha session options.
     * @param string $ipAddress The IP address of the visitor.
     * @param string $widgetId The captcha widget identifier.
     * @param string|null $challengeId The captcha challenge identifier.
     */
    public function __construct(KeyValueStorageInterface $storage, array $options, string $ipAddress, string $widgetId, string $challengeId = null)
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
                $this->storage->read("$this->key.$this->widgetId:$this->challengeId")
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
        // Write the session data to the storage container.
        $this->storage->write("$this->key.$this->widgetId:$this->challengeId", $this->puzzle->toArray());
    }

    /**
     * @inheritDoc
     */
    public function destroy(): void
    {
        // Clear all data linked to the current session.
        $this->storage->remove("$this->key.$this->widgetId:$this->challengeId");
    }

    /**
     * @inheritDoc
     */
    protected function purgeExpired(): void
    {
        // If no session is set yet, do nothing.
        if (!$this->storage->exists($this->key)) {
            return;
        }

        // Check all existing sessions, deleting the ones which are expired.
        foreach ($this->storage->read($this->key) as $id => $session) {
            if ($session['expiresAt'] > 0 && $session['expiresAt'] < Utils::getCurrentTimeInMilliseconds()) {
                $this->storage->remove("$this->key.$id");
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function exists(string $challengeId, string $widgetId): bool
    {
        return !empty($challengeId) && !empty($widgetId)
            && $this->storage->exists("$this->key.$widgetId:$challengeId");
    }
}
