<?php

namespace IconCaptcha\Attempts\Drivers\Database;

use IconCaptcha\Attempts\Attempts;
use IconCaptcha\Attempts\Drivers\Database\Query\QueryInterface;
use IconCaptcha\Storage\Database\PDOStorageInterface;
use IconCaptcha\Utils;

class PDOAttempts extends Attempts
{
    /**
     * @var string The default table name for the attempts.
     */
    protected string $table = 'iconcaptcha_attempts';

    /**
     * @var PDOStorageInterface The database storage wrapper.
     */
    private PDOStorageInterface $storage;

    /**
     * @var QueryInterface The query strategy to use when performing database operation.
     */
    private QueryInterface $queryStrategy;

    /**
     * @var string The IP address of the visitor.
     */
    protected string $ipAddress;

    public function __construct(PDOStorageInterface $storage, QueryInterface $queryStrategy, array $options, string $ipAddress)
    {
        parent::__construct($options);

        $this->storage = $storage;
        $this->queryStrategy = $queryStrategy;
        $this->ipAddress = $ipAddress;

        // Change the table name, if set in the options.
        $storageOptions = $this->options['storage']['options'];
        if (isset($storageOptions['table']) && is_string($storageOptions['table'])) {
            $this->table = $storageOptions['table'];
        }

        $this->purgeExpiredAttempts();
    }

    /**
     * @inheritDoc
     */
    public function increaseAttempts(): void
    {
        // Read the current attempts count.
        $storedAttemptsCount = $this->getCurrentAttemptsCount();
        $updatedAttemptsCount = $storedAttemptsCount + 1 ?? 1;

        // If the attempts threshold was exceeded, issue a timeout.
        // Otherwise, only register the attempt.
        if($updatedAttemptsCount >= $this->options['amount']) {
            $this->issueTimeout();
        } else {
            $validityTimestamp = $this->storage->formatTimestampAsDatetime(
                $this->getNewValidityTimestamp()
            );

            // Depending on if attempts were already registered, we either update
            // the attempts counter of the existing record, or insert a new one.
            if(is_null($storedAttemptsCount)) {
                // TODO if inserting fails, try increasing the attempts.
                $this->storage->getConnection()->prepare(
                    $this->queryStrategy->insertAttemptQuery($this->table)
                )->execute([
                    $this->ipAddress,
                    $updatedAttemptsCount,
                    $validityTimestamp,
                ]);
            } else {
                // TODO if increasing fails, try inserting a new attempt.
                $this->storage->getConnection()->prepare(
                    $this->queryStrategy->increaseAttemptsQuery($this->table)
                )->execute([
                    $updatedAttemptsCount,
                    $validityTimestamp,
                    $this->ipAddress,
                ]);
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function clearAttempts(): void
    {
        $this->storage->getConnection()->prepare(
            $this->queryStrategy->clearAttemptsQuery($this->table)
        )->execute([
            $this->ipAddress,
        ]);
    }

    /**
     * @inheritDoc
     */
    protected function issueTimeout(): bool
    {
        // Calculate the timeout period. The timeout will be active until the timestamp has expired.
        $timeoutTimestamp = $this->options['timeout'] + Utils::getCurrentTimeInSeconds();
        $timeoutDate = $this->storage->formatTimestampAsDatetime($timeoutTimestamp);

        // Store the timeout.
        $this->storage->getConnection()->prepare(
            $this->queryStrategy->issueTimeoutQuery($this->table)
        )->execute([
            $timeoutDate,
            $timeoutDate,
            $this->ipAddress,
        ]);

        return true;
    }

    /**
     * @inheritDoc
     */
    protected function getActiveTimeoutTimestamp(): ?int
    {
        $statement = $this->storage->getConnection()->prepare(
            $this->queryStrategy->activeTimeoutTimestampQuery($this->table)
        );

        $statement->execute([
            $this->ipAddress,
            $this->storage->getDatetime(),
        ]);

        $timestamp = $statement->fetchColumn();

        return !empty($timestamp) ? strtotime($timestamp) : null;
    }

    /**
     * @inheritDoc
     */
    protected function getCurrentAttemptsCount(): ?int
    {
        $statement = $this->storage->getConnection()->prepare(
            $this->queryStrategy->currentAttemptsCountQuery($this->table)
        );

        $statement->execute([
            $this->ipAddress,
            $this->storage->getDatetime(),
        ]);

        $attemptsCount = $statement->fetchColumn();

        return ($attemptsCount !== false) ? (int)$attemptsCount : null;
    }

    /**
     * @inheritDoc
     */
    protected function getAttemptsValidityTimestamp(): ?int
    {
        $statement = $this->storage->getConnection()->prepare(
            $this->queryStrategy->attemptsValidityTimestampQuery($this->table)
        );

        $statement->execute([
            $this->ipAddress,
        ]);

        $timestamp = $statement->fetchColumn();

        return !empty($timestamp) ? strtotime($timestamp) : 0;
    }

    /**
     * Deletes all expired attempts/timeouts.
     */
    protected function purgeExpiredAttempts(): void
    {
        $this->storage->getConnection()->prepare(
            $this->queryStrategy->purgeExpiredTimeoutsQuery($this->table)
        )->execute([
            $this->storage->getDatetime(),
        ]);
    }
}
