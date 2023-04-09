<?php

namespace IconCaptcha\Session\Drivers\Database;

use IconCaptcha\Session\Exceptions\SessionDatabaseOptionsException;
use IconCaptcha\Session\Session;
use PDO;

abstract class PDOSession extends Session
{
    private const DEFAULT_PDO_OPTIONS = [
        PDO::ATTR_CASE => PDO::CASE_NATURAL,
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ];

    private const DATE_FORMAT = 'Y-m-d H:i:s';

    /**
     * @var string The default table name for the session data.
     */
    protected string $table = 'sessions';

    /**
     * @var bool Whether the current session data was saved to the database before.
     */
    private bool $existingSession = false;

    private PDO $connection;

    public function __construct(array $options, string $ipAddress, string $widgetId, string $challengeId = null)
    {
        parent::__construct($options, $ipAddress, $widgetId, $challengeId);

        $this->connection = $this->createConnection();

        // Change the table name, if set in the options.
        if (isset($this->options['connection']['table']) && is_string($this->options['connection']['table'])) {
            $this->table = $this->options['connection']['table'];
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
                $this->loadSessionQuery()
            );

            $statement->execute([
                $this->widgetId,
                $this->challengeId
            ]);

            $record = $statement->fetchObject();

            $this->data->fromJson($record->data);
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
                $this->saveSessionQuery()
            );

            $statement->execute([
                $this->data->toJson(),
                $this->getDbFormattedExpirationTime(),
                $this->widgetId,
                $this->challengeId,
            ]);
        } else {
            $statement = $this->connection->prepare(
                $this->createSessionQuery()
            );

            $this->existingSession = $statement->execute([
                $this->widgetId,
                $this->challengeId,
                $this->data->toJson(),
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
            $this->destroySessionQuery()
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
            $this->purgeExpiredSessionsQuery()
        )->execute([
            date(self::DATE_FORMAT)
        ]);
    }

    /**
     * @inheritDoc
     */
    public function exists(string $challengeId, string $widgetId): bool
    {
        $statement = $this->connection->prepare(
            $this->sessionExistsQuery()
        );

        $statement->execute([
            $widgetId,
            $challengeId
        ]);

        return (bool)$statement->fetchColumn();
    }

    /**
     * Establish a database connection. If an existing connection already exists,
     * this connection will be reused instead of establishing a new one.
     */
    private function createConnection(): PDO
    {
        // If an open database connection already exists, reuse it.
        if (!empty($this->connection)) {
            return $this->connection;
        }

        $config = $this->options['connection'];

        // In case the config is an existing PDO object, simply return it.
        if ($config instanceof PDO) {
            $this->connection = $config;
            return $config;
        }

        if (is_array($config)) {

            // If a DSN string (URL) is specified, simply return it. At this
            // point assume that the DSN string is a correct, valid DSN
            // string, and that no further adjustments have to be made to it.
            if (!empty($config['url'])) {
                $dsnString = $config['url'];
            } else {
                $dsnString = $this->createDsnString($config);
            }

            return new PDO($dsnString, $config['username'] ?? null, $config['password'] ?? null, $this->getConnectionOptions($config));
        }

        throw new SessionDatabaseOptionsException($config);
    }

    /**
     * Returns the session expiration timestamp as a formatted datetime string, suitable for storing in a database.
     * @return string|null The formatted datetime string, or NULL if the session expiration time is not set.
     */
    private function getDbFormattedExpirationTime(): ?string
    {
        return $this->expiresAt > 0
            ? date(self::DATE_FORMAT, floor($this->expiresAt / 1000)) // database timestamps are in seconds, not milliseconds.
            : null;
    }

    /**
     * @param array $config
     * @return array
     */
    private function getConnectionOptions(array $config): array
    {
        $options = $config['options'] ?? [];

        return array_diff_key(self::DEFAULT_PDO_OPTIONS, $options) + $options;
    }

    /**
     * Returns the database query to fetch a single session.
     *
     * The query is expected to fetch a single record, and select the 'data' and 'expires_at' columns, as
     * well as supply binding placeholders for the 'widget_id' and 'challenge_id' in the WHERE condition.
     *
     * @example
     * SELECT statement: 'data, expires_at'
     * WHERE condition with bindings: 'widget_id = ? AND challenge_id = ?'
     */
    abstract protected function loadSessionQuery(): string;

    /**
     * Returns the database query to update an existing session.
     *
     * The query is expected to supply binding placeholders for the following columns, in
     * this exact order: data, expires_at, widget_id, challenge_id
     *
     * The binding placeholders for the 'widget_id' and 'challenge_id' columns are expected to be part of
     * the WHERE condition, whereas the 'data' and 'expires_at' placeholders are part of the UPDATE statement.
     *
     * @example
     * SET statement with bindings: 'data = ?, expires_at = ?'
     * WHERE condition with bindings: 'widget_id = ? AND challenge_id = ?'
     */
    abstract protected function saveSessionQuery(): string;

    /**
     * Returns the database query to insert a new session record.
     *
     * The query is expected to supply binding placeholders for the following columns, in
     * this exact order: widget_id, challenge_id, data, expires_at
     *
     * @example VALUES statement with bindings: '(?, ?, ?, ?)'
     */
    abstract protected function createSessionQuery(): string;

    /**
     * Returns the database query to delete a session record.
     *
     * The query is expected to supply binding placeholders for the 'widget_id' and 'challenge_id'.
     * These should be used in the WHERE condition to ensure the correct record will be deleted.
     *
     * @example WHERE condition with bindings: 'widget_id = ? AND challenge_id = ?'
     */
    abstract protected function destroySessionQuery(): string;

    /**
     * Returns the database query to delete expired sessions.
     *
     * The query is expected to supply a binding placeholder for the 'expires_at' column.
     * This column should be used in the WHERE condition to ensure the correct records will be deleted.
     *
     * @example WHERE condition with binding: 'expires_at < ?'
     */
    abstract protected function purgeExpiredSessionsQuery(): string;

    /**
     * Returns the database query to check if a session exists.
     *
     * The query is expected to request only 1 record, and supply binding placeholders for the 'widget_id' and 'challenge_id' columns.
     * These should be used in the WHERE condition to ensure that the existence of the correct session is checked.
     *
     * @example WHERE condition with bindings: 'widget_id = ? AND challenge_id = ?'
     */
    abstract protected function sessionExistsQuery(): string;

    /**
     * Creates a DSN string from connection configuration details.
     */
    abstract protected function createDsnString(array $config): string;
}
