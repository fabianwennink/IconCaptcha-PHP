<?php

namespace IconCaptcha\Session\Drivers\Database;

use IconCaptcha\Exceptions\FileNotFoundException;

class SQLiteSession extends PDOSession
{
    /**
     * @inheritDoc
     * @throws FileNotFoundException
     */
    public function createDsnString(array $config): string
    {
        if ($config['database'] === ':memory:') {
            return 'sqlite::memory:';
        }

        $path = realpath($config['database']);

        // Verify that the SQLite database exists before attempting to connect.
        // The SQLite driver will not throw any exception by default if the file does not exist.
        if ($path === false) {
            throw new FileNotFoundException($config['database']);
        }

        return "sqlite:$path";
    }

    /**
     * @inheritDoc
     */
    protected function loadSessionQuery(): string
    {
        return "SELECT puzzle, expires_at FROM $this->table WHERE widget_id = ? AND challenge_id = ? LIMIT 1;";
    }

    /**
     * @inheritDoc
     */
    protected function saveSessionQuery(): string
    {
        return "UPDATE $this->table SET puzzle = ?, expires_at = ? WHERE widget_id = ? AND challenge_id = ?;";
    }

    /**
     * @inheritDoc
     */
    protected function createSessionQuery(): string
    {
        return "INSERT INTO $this->table (widget_id, challenge_id, puzzle, expires_at, ip_address) VALUES (?, ?, ?, ?, ?);";
    }

    /**
     * @inheritDoc
     */
    protected function destroySessionQuery(): string
    {
        return "DELETE FROM $this->table WHERE widget_id = ? AND challenge_id = ?;";
    }

    /**
     * @inheritDoc
     */
    protected function purgeExpiredSessionsQuery(): string
    {
        return "DELETE FROM $this->table WHERE expires_at IS NOT NULL AND expires_at < ?;";
    }

    /**
     * @inheritDoc
     */
    protected function sessionExistsQuery(): string
    {
        return "SELECT 1 FROM $this->table WHERE widget_id = ? AND challenge_id = ? LIMIT 1;";
    }
}
