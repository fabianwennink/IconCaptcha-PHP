<?php

/*
 * IconCaptcha - Copyright 2023, Fabian Wennink (https://www.fabianwennink.nl)
 * Licensed under the MIT license: https://www.fabianwennink.nl/projects/IconCaptcha/license
 *
 * The above copyright notice and license shall be included in all copies or substantial portions of the software.
 */

namespace IconCaptcha\Session\Drivers\Database;

use IconCaptcha\Session\Drivers\Database\Query\QueryInterface;
use IconCaptcha\Session\Session;
use IconCaptcha\Storage\Database\PDOStorageInterface;

class PDOSession extends Session
{
    /**
     * @var string The default table name for the session data.
     */
    protected string $table = 'iconcaptcha_challenges';

    /**
     * @var bool Whether the current session data was saved to the database before.
     */
    private bool $existingSession = false;

    /**
     * @var PDOStorageInterface The database storage container.
     */
    private PDOStorageInterface $storage;

    /**
     * @var QueryInterface The query strategy to use when performing database operation.
     */
    private QueryInterface $queryStrategy;

    /**
     * Creates a new database (PDO) session instance.
     *
     * @param PDOStorageInterface $storage The database storage container.
     * @param QueryInterface $queryStrategy The query strategy to use.
     * @param array $options The captcha session options.
     * @param string $ipAddress The IP address of the visitor.
     * @param string $widgetId The captcha widget identifier.
     * @param string|null $challengeId The captcha challenge identifier.
     */
    public function __construct(
        PDOStorageInterface $storage,
        QueryInterface $queryStrategy,
        array $options,
        string $ipAddress,
        string $widgetId,
        string $challengeId = null
    )
    {
        parent::__construct($options, $ipAddress, $widgetId, $challengeId);

        $this->storage = $storage;
        $this->queryStrategy = $queryStrategy;

        // Change the table name, if set in the options.
        if (isset($this->options['options']['table']) && is_string($this->options['options']['table'])) {
            $this->table = $this->options['options']['table'];
        }

        // Purge any expired attempts records, if enabled.
        if (!isset($this->options['options']['purging']) || $this->options['options']['purging']) {
            $this->purgeExpired();
        }

        $this->load();
    }

    /**
     * @inheritDoc
     */
    public function load(): void
    {
        if ($this->exists($this->challengeId ?? '', $this->widgetId)) {
            $statement = $this->storage->getConnection()->prepare(
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
            $statement = $this->storage->getConnection()->prepare(
                $this->queryStrategy->saveQuery($this->table)
            );

            $statement->execute([
                $this->puzzle->toJson(),
                $this->getDbFormattedExpirationTime(),
                $this->widgetId,
                $this->challengeId,
            ]);
        } else {
            $statement = $this->storage->getConnection()->prepare(
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
        $this->storage->getConnection()->prepare(
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
        $this->storage->getConnection()->prepare(
            $this->queryStrategy->purgeQuery($this->table)
        )->execute([
            $this->storage->getDatetime(),
        ]);
    }

    /**
     * @inheritDoc
     */
    public function exists(string $challengeId, string $widgetId): bool
    {
        $statement = $this->storage->getConnection()->prepare(
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
     *
     * @return string|null The formatted datetime string, or NULL if the session expiration time is not set.
     */
    private function getDbFormattedExpirationTime(): ?string
    {
        return $this->expiresAt > 0
            // Database timestamps are in seconds, not milliseconds.
            ? $this->storage->formatTimestampAsDatetime(floor($this->expiresAt / 1000))
            : null;
    }
}
