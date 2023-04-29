<?php

namespace IconCaptcha\Attempts\Drivers;

use IconCaptcha\Attempts\Attempts;
use IconCaptcha\Storage\Session\SessionStorage;

class SessionAttempts extends Attempts
{
    /**
     * @var string The key used to store the attempts at in the session.
     */
    private string $sessionKey = 'attempts';

    private SessionStorage $storage;

    public function __construct(SessionStorage $storage, array $options)
    {
        parent::__construct($options);

        $this->storage = $storage;
    }

    /**
     * @inheritDoc
     */
    public function increaseAttempts(int $timestamp): void
    {
        // Read the current attempts count from session. Use 0 if no count is stored.
        $storedAttemptsCount = $this->getCurrentAttemptsCount();
        $updatedAttemptsCount = $storedAttemptsCount + 1;

        // Store the updated attempts count.
        $this->storage->write($this->sessionKey, [
            'count' => $updatedAttemptsCount,
            'valid' => $this->getNewValidityTimestamp(),
        ]);

        // If the attempts threshold was passed, issue a timeout.
        if($updatedAttemptsCount >= $this->options['amount']) {
            $this->issueTimeout($timestamp);
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
    protected function issueTimeout(int $currentTimestamp): bool
    {
        // Calculate the timeout period. The timeout will be active until the timestamp has expired.
        $timeoutMilliseconds = ($this->options['timeout'] * 1000) + $currentTimestamp;

        // Store the timeout in the session.
        $this->storage->write($this->sessionKey, [
            'timeout' => $timeoutMilliseconds,
            // Instead of setting a new timestamp, simply re-use the timeout timestamp.
            // This way the attempts will invalidate after the timeout has been lifted.
            'valid' => $timeoutMilliseconds,
        ]);

        return true;
    }

    /**
     * @inheritDoc
     */
    protected function getActiveTimeoutTimestamp(): ?int
    {
        // Read the active timeout timestamp from session, if still valid.
        if($this->isAttemptsDataStillValid()) {
            return $this->storage->read("$this->sessionKey.timeout");
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    protected function getCurrentAttemptsCount(): int
    {
        // Read the active timeout timestamp from session, if still valid.
        if($this->isAttemptsDataStillValid()) {
            return $this->storage->read("$this->sessionKey.count") ?? 0;
        }

        return 0;
    }

    /**
     * @inheritDoc
     */
    protected function getAttemptsValidityTimestamp(): ?int
    {
        return $this->storage->read("$this->sessionKey.valid");
    }
}
