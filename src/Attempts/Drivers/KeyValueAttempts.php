<?php

/*
 * IconCaptcha - Copyright 2023, Fabian Wennink (https://www.fabianwennink.nl)
 * Licensed under the MIT license: https://www.fabianwennink.nl/projects/IconCaptcha/license
 *
 * The above copyright notice and license shall be included in all copies or substantial portions of the software.
 */

namespace IconCaptcha\Attempts\Drivers;

use IconCaptcha\Attempts\Attempts;
use IconCaptcha\Storage\KeyValueStorageInterface;
use IconCaptcha\Utils;

class KeyValueAttempts extends Attempts
{
    /**
     * @var string The key used to store the attempts at in the storage container.
     */
    private string $key = 'attempts';

    /**
     * @var KeyValueStorageInterface The storage container.
     */
    private KeyValueStorageInterface $storage;

    /**
     * Creates a new instance of the attempts and timeout manager.
     *
     * @param KeyValueStorageInterface $storage The storage container.
     * @param array $options The captcha storage options.
     */
    public function __construct(KeyValueStorageInterface $storage, array $options)
    {
        parent::__construct($options);

        $this->storage = $storage;
    }

    /**
     * @inheritDoc
     */
    public function increaseAttempts(): void
    {
        // Read the current attempts count.
        $storedAttemptsCount = $this->getCurrentAttemptsCount();
        $updatedAttemptsCount = $storedAttemptsCount + 1 ?? 1;

        // If the attempts threshold was passed, issue a timeout.
        // Otherwise, only increment the attempts counter.
        if ($updatedAttemptsCount >= $this->options['amount']) {
            $this->issueTimeout();
        } else {
            $this->storage->write($this->key, [
                'count' => $updatedAttemptsCount,
                'valid' => $this->getNewValidityTimestamp(),
            ]);
        }
    }

    /**
     * @inheritDoc
     */
    public function clearAttempts(): void
    {
        $this->storage->remove($this->key);
    }

    /**
     * @inheritDoc
     */
    protected function issueTimeout(): bool
    {
        // Calculate the timeout period. The timeout will be active until the timestamp has expired.
        $timeoutTimestamp = $this->options['timeout'] + Utils::getCurrentTimeInSeconds();

        // Store the timeout in the session.
        $this->storage->write($this->key, [
            'timeout' => $timeoutTimestamp,
            // Instead of setting a new timestamp, simply re-use the timeout timestamp.
            // This way the attempts will invalidate after the timeout has been lifted.
            'valid' => $timeoutTimestamp,
        ]);

        return true;
    }

    /**
     * @inheritDoc
     */
    protected function getActiveTimeoutTimestamp(): ?int
    {
        if ($this->isAttemptsDataStillValid()) {
            return $this->storage->read("$this->key.timeout");
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    protected function getCurrentAttemptsCount(): ?int
    {
        if ($this->isAttemptsDataStillValid()) {
            return $this->storage->read("$this->key.count");
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    protected function getAttemptsValidityTimestamp(): ?int
    {
        return $this->storage->read("$this->key.valid");
    }
}
