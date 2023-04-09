<?php

namespace IconCaptcha\Session\Drivers\Database;

class PostgresSession extends PDOSession
{
    /**
     * @inheritDoc
     */
    public function createDsnString(array $config): string
    {
        $dsn = "pgsql:host={$config['host']};dbname={$config['database']}";

        // If a port was specified, add it to the DSN string.
        if (isset($config['port'])) {
            $dsn .= ";port={$config['port']}";
        }

        return $dsn;
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
