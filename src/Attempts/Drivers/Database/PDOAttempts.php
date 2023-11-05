<?php

/*
 * IconCaptcha - Copyright 2023, Fabian Wennink (https://www.fabianwennink.nl)
 * Licensed under the MIT license: https://www.fabianwennink.nl/projects/IconCaptcha/license
 *
 * The above copyright notice and license shall be included in all copies or substantial portions of the software.
 */

namespace IconCaptcha\Attempts\Drivers\Database;

use IconCaptcha\Attempts\Attempts;
use IconCaptcha\Attempts\Drivers\Database\Query\QueryInterface;
use IconCaptcha\Storage\Database\PDOStorageInterface;
use IconCaptcha\Utils;
use PDOException;

class PDOAttempts extends Attempts
{
    /**
     * @var string The default table name for the attempts.
     */
    protected string $table = 'iconcaptcha_attempts';

    /**
     * @var PDOStorageInterface The database storage container.
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

    /**
     * Creates a new database (PDO) attempts/timeout manager instance.
     *
     * @param PDOStorageInterface $storage The database storage container.
     * @param QueryInterface $queryStrategy The query strategy to use.
     * @param array $options The captcha storage options.
     * @param string $ipAddress The IP address of the visitor.
     */
    public function __construct(PDOStorageInterface $storage, QueryInterface $queryStrategy, array $options, string $ipAddress)
    {
        parent::__construct($options);

        $this->storage = $storage;
        $this->queryStrategy = $queryStrategy;
        $this->ipAddress = $ipAddress;

        // Change the table name, if set in the options.
        if (isset($this->options['storage']['options']['table']) && is_string($this->options['storage']['options']['table'])) {
            $this->table = $this->options['storage']['options']['table'];
        }

        // Purge any expired attempts records, if enabled.
        if (!isset($this->options['storage']['options']['purging']) || $this->options['storage']['options']['purging']) {
            $this->purgeExpiredAttempts();
        }
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
        if ($updatedAttemptsCount >= $this->options['amount']) {
            $this->issueTimeout();
        } else {
            $validityTimestamp = $this->storage->formatTimestampAsDatetime(
                $this->getNewValidityTimestamp()
            );

            // Depending on if attempts were already registered, we either update
            // the attempts counter of the existing record, or insert a new one.
            if (is_null($storedAttemptsCount)) {
                // No previous attempts were found, try to insert a new record.
                if (!$this->performAttemptsInsert($updatedAttemptsCount, $validityTimestamp)) {
                    // Insertion failed, perform increase query.
                    $this->performAttemptsIncrease($updatedAttemptsCount, $validityTimestamp);
                }
            } else {
                // Previous attempts were found, perform increase query.
                if (!$this->performAttemptsIncrease($updatedAttemptsCount, $validityTimestamp)) {
                    // Increase query failed, insert a new record.
                    $this->performAttemptsInsert($updatedAttemptsCount, $validityTimestamp);
                }
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

        return $attemptsCount !== false ? (int)$attemptsCount : null;
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

    /**
     * Performs an attempt insert query. For this query to succeed, no existing
     * record must exist in the database for the visitor's IP address.
     *
     * @param int $updatedAttemptsCount The updated attempts count.
     * @param string $validityTimestamp The validity timestamp, formatted as a datetime string.
     * @return bool TRUE if the insert query was successful, FALSE otherwise.
     */
    private function performAttemptsInsert(int $updatedAttemptsCount, string $validityTimestamp): bool
    {
        try {
            return $this->storage->getConnection()->prepare(
                $this->queryStrategy->insertAttemptQuery($this->table)
            )->execute([
                $this->ipAddress,
                $updatedAttemptsCount,
                $validityTimestamp,
            ]);
        } catch (PDOException $exception) {
            return false;
        }
    }

    /**
     * Performs an attempt increase query. For this query to succeed, an existing
     * record must exist in the database for the visitor's IP address.
     *
     * @param int $updatedAttemptsCount The updated attempts count.
     * @param string $validityTimestamp The validity timestamp, formatted as a datetime string.
     * @return bool TRUE if the increase query was successful, FALSE otherwise.
     */
    private function performAttemptsIncrease(int $updatedAttemptsCount, string $validityTimestamp): bool
    {
        try {
            return $this->storage->getConnection()->prepare(
                $this->queryStrategy->increaseAttemptsQuery($this->table)
            )->execute([
                $updatedAttemptsCount,
                $validityTimestamp,
                $this->ipAddress,
            ]);
        } catch (PDOException $exception) {
            return false;
        }
    }
}
