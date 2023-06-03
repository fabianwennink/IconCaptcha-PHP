<?php

namespace IconCaptcha\Session\Drivers\Database;

use IconCaptcha\Session\Drivers\Database\Query\QueryInterface;
use IconCaptcha\Session\Session;
use PDO;

class PDOSession extends Session
{
    private const DEFAULT_DATE_FORMAT = 'Y-m-d H:i:s';

    /**
     * @var string The default table name for the session data.
     */
    protected string $table = 'iconcaptcha_challenges';

    /**
     * @var bool Whether the current session data was saved to the database before.
     */
    private bool $existingSession = false;

    /**
     * @var PDO The database connection.
     */
    private PDO $connection;

    /**
     * @var QueryInterface The query strategy to use when performing database operation.
     */
    private QueryInterface $queryStrategy;

    public function __construct(PDO $storage, QueryInterface $queryStrategy, array $options, string $ipAddress, string $widgetId, string $challengeId = null)
    {
        parent::__construct($options, $ipAddress, $widgetId, $challengeId);

        $this->connection = $storage;
        $this->queryStrategy = $queryStrategy;

        // Change the table name, if set in the options.
        if (isset($this->options['options']['table']) && is_string($this->options['options']['table'])) {
            $this->table = $this->options['options']['table'];
        }

        $this->purgeExpired();
        $this->load();
    }

    /**
     * @inheritDoc
     */
    public function load(): void
    {
        if ($this->exists($this->challengeId ?? '', $this->widgetId)) {
            $statement = $this->connection->prepare(
                $this->queryStrategy->loadQuery($this->table)
            );

            $statement->execute([
                $this->widgetId,
                $this->challengeId
            ]);

            $record = $statement->fetchObject();

            $this->puzzle->fromJson($record->puzzle);
            $this->existingSession = true;
        } else {
            $this->challengeId = $this->challengeId ?? $this->generateUniqueId();
            $this->existingSession = false;
        }

        $this->dataLoaded = true;
    }

    /**
     * @inheritDoc
     */
    public function save(): void
    {
        if ($this->existingSession) {
            $statement = $this->connection->prepare(
                $this->queryStrategy->saveQuery($this->table)
            );

            $statement->execute([
                $this->puzzle->toJson(),
                $this->getDbFormattedExpirationTime(),
                $this->widgetId,
                $this->challengeId,
            ]);
        } else {
            $statement = $this->connection->prepare(
                $this->queryStrategy->createQuery($this->table)
            );

            $this->existingSession = $statement->execute([
                $this->widgetId,
                $this->challengeId,
                $this->puzzle->toJson(),
                $this->getDbFormattedExpirationTime(),
                $this->ipAddress,
            ]);
        }
    }

    /**
     * @inheritDoc
     */
    public function destroy(): void
    {
        $this->connection->prepare(
            $this->queryStrategy->destroyQuery($this->table)
        )->execute([
            $this->widgetId,
            $this->challengeId
        ]);
    }

    /**
     * @inheritDoc
     */
    public function purgeExpired(): void
    {
        $this->connection->prepare(
            $this->queryStrategy->purgeQuery($this->table)
        )->execute([
            date(self::DEFAULT_DATE_FORMAT)
        ]);
    }

    /**
     * @inheritDoc
     */
    public function exists(string $challengeId, string $widgetId): bool
    {
        $statement = $this->connection->prepare(
            $this->queryStrategy->existsQuery($this->table)
        );

        $statement->execute([
            $widgetId,
            $challengeId
        ]);

        return (bool)$statement->fetchColumn();
    }

    /**
     * Returns the session expiration timestamp as a formatted datetime string, suitable for storing in a database.
     * @return string|null The formatted datetime string, or NULL if the session expiration time is not set.
     */
    private function getDbFormattedExpirationTime(): ?string
    {
        return $this->expiresAt > 0
            ? date(self::DEFAULT_DATE_FORMAT, floor($this->expiresAt / 1000)) // database timestamps are in seconds, not milliseconds.
            : null;
    }
}
