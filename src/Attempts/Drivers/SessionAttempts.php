<?php

namespace IconCaptcha\Attempts\Drivers;

use IconCaptcha\Attempts\Attempts;
use IconCaptcha\Storage\Session\SessionStorage;
use IconCaptcha\Utils;

class SessionAttempts extends Attempts
{
    /**
     * @var string The key used to store the attempts at in the session.
     */
    private string $sessionKey = 'attempts';

    /**
     * @var SessionStorage The session storage wrapper.
     */
    private SessionStorage $storage;

    public function __construct(SessionStorage $storage, array $options)
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
        if($updatedAttemptsCount >= $this->options['amount']) {
            $this->issueTimeout();
        } else {
            $this->storage->write($this->sessionKey, [
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
        // Remove the attempts key from the session.
        $this->storage->remove($this->sessionKey);
    }

    /**
     * @inheritDoc
     */
    protected function issueTimeout(): bool
    {
        // Calculate the timeout period. The timeout will be active until the timestamp has expired.
        $timeoutTimestamp = $this->options['timeout'] + Utils::getCurrentTimeInSeconds();

        // Store the timeout in the session.
        $this->storage->write($this->sessionKey, [
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
        if($this->isAttemptsDataStillValid()) {
            return $this->storage->read("$this->sessionKey.timeout");
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    protected function getCurrentAttemptsCount(): ?int
    {
        if($this->isAttemptsDataStillValid()) {
            return $this->storage->read("$this->sessionKey.count");
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    protected function getAttemptsValidityTimestamp(): ?int
    {
        return $this->storage->read("$this->sessionKey.valid");
    }
}
