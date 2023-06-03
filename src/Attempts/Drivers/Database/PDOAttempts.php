<?php

namespace IconCaptcha\Attempts\Drivers\Database;

use IconCaptcha\Attempts\Attempts;
use IconCaptcha\Attempts\Drivers\Database\Query\QueryInterface;
use PDO;

class PDOAttempts extends Attempts
{
    private const DEFAULT_DATE_FORMAT = 'Y-m-d H:i:s';

    /**
     * @var string The default table name for the attempts.
     */
    protected string $table = 'iconcaptcha_attempts';

    /**
     * @var PDO The database connection.
     */
    private PDO $connection;

    /**
     * @var QueryInterface The query strategy to use when performing database operation.
     */
    private QueryInterface $queryStrategy;

    /**
     * @var string The IP address of the visitor.
     */
    protected string $ipAddress;

    public function __construct(PDO $storage, QueryInterface $queryStrategy, array $options, string $ipAddress)
    {
        parent::__construct($options);

        $this->connection = $storage;
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
    public function increaseAttempts(int $timestamp): void
    {
        // TODO refactor, duplicate date conversion + millisecond requirement
        // Read the current attempts count.
        $storedAttemptsCount = $this->getCurrentAttemptsCount();
        $updatedAttemptsCount = $storedAttemptsCount + 1 ?? 1;

        // If the attempts threshold was exceeded, issue a timeout.
        // Otherwise, only register the attempt.
        if($updatedAttemptsCount >= $this->options['amount']) {
            $this->issueTimeout($timestamp);
        } else {
            $validityTimestamp = date(self::DEFAULT_DATE_FORMAT, floor($this->getNewValidityTimestamp() / 1000));

            // Depending on if attempts were already registered, we either update
            // the attempts counter of the existing record, or insert a new one.
            if(is_null($storedAttemptsCount)) {
                // TODO if inserting fails, try increasing the attempts.
                $this->connection->prepare(
                    $this->queryStrategy->insertAttemptQuery($this->table)
                )->execute([
                    $this->ipAddress,
                    $updatedAttemptsCount,
                    $validityTimestamp,
                ]);
            } else {
                // TODO if increasing fails, try inserting a new attempt.
                $this->connection->prepare(
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
        $this->connection->prepare(
            $this->queryStrategy->clearAttemptsQuery($this->table)
        )->execute([
            $this->ipAddress,
        ]);
    }

    /**
     * @inheritDoc
     */
    protected function issueTimeout(int $currentTimestamp): bool
    {
        // TODO refactor, duplicate date conversion + millisecond requirement
        // Calculate the timeout period. The timeout will be active until the timestamp has expired.
        $timeoutMilliseconds = ($this->options['timeout'] * 1000) + $currentTimestamp;
        $timeoutDate = date(self::DEFAULT_DATE_FORMAT, floor($timeoutMilliseconds / 1000));

        // Store the timeout.
        $this->connection->prepare(
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
        $statement = $this->connection->prepare(
            $this->queryStrategy->activeTimeoutTimestampQuery($this->table)
        );

        $statement->execute([
            $this->ipAddress,
            date(self::DEFAULT_DATE_FORMAT),
        ]);

        $timestamp = $statement->fetchColumn();

        return !empty($timestamp) ? strtotime($timestamp) * 1000 : null; // TODO refactor millisecond requirement
    }

    /**
     * @inheritDoc
     */
    protected function getCurrentAttemptsCount(): ?int
    {
        $statement = $this->connection->prepare(
            $this->queryStrategy->currentAttemptsCountQuery($this->table)
        );

        $statement->execute([
            $this->ipAddress,
            date(self::DEFAULT_DATE_FORMAT),
        ]);

        $attemptsCount = $statement->fetchColumn();

        return ($attemptsCount !== false) ? (int)$attemptsCount : null;
    }

    /**
     * @inheritDoc
     */
    protected function getAttemptsValidityTimestamp(): ?int
    {
        $statement = $this->connection->prepare(
            $this->queryStrategy->attemptsValidityTimestampQuery($this->table)
        );

        $statement->execute([
            $this->ipAddress,
        ]);

        $timestamp = $statement->fetchColumn();

        return !empty($timestamp) ? strtotime($timestamp) * 1000 : 0; // TODO refactor millisecond requirement
    }

    /**
     * Deletes all expired attempts/timeouts.
     */
    protected function purgeExpiredAttempts(): void
    {
        $this->connection->prepare(
            $this->queryStrategy->purgeExpiredTimeoutsQuery($this->table)
        )->execute([
            date(self::DEFAULT_DATE_FORMAT)
        ]);
    }
}
