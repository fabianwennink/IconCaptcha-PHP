<?php

/**
 * IconCaptcha Plugin: v3.1.1
 * Copyright © 2022, Fabian Wennink (https://www.fabianwennink.nl)
 *
 * Licensed under the MIT license: https://www.fabianwennink.nl/projects/IconCaptcha/license
 */

namespace IconCaptcha\Session\Drivers\Database;

use PDO;

class SqlServerSession extends PDOSession
{
    /**
     * @inheritDoc
     */
    public function createDsnString(array $config): string
    {
        // If the ODBC source is set and the ODBC driver is available, create an ODBC DSN string.
        if (isset($config['odbc']) && in_array('odbc', PDO::getAvailableDrivers(), true)) {
            return isset($config['port'])
                ? "odbc:Driver={$config['odbc']};Server={$config['host']},{$config['port']};Database={$config['database']};"
                : "odbc:Driver={$config['odbc']};Server={{$config['host']};Database={$config['database']};";
        }

        return isset($config['port'])
            ? "sqlsrv:server={$config['host']},{$config['port']};database={$config['database']};"
            : "sqlsrv:server={{$config['host']};database={$config['database']};";
    }

    /**
     * @inheritDoc
     */
    protected function loadSessionQuery(): string
    {
        return "SELECT TOP 1 data, expires_at FROM $this->table WHERE widget_id = ? AND challenge_id = ?;";
    }

    /**
     * @inheritDoc
     */
    protected function saveSessionQuery(): string
    {
        return "UPDATE $this->table SET data = ?, expires_at = ? WHERE widget_id = ? AND challenge_id = ?;";
    }

    /**
     * @inheritDoc
     */
    protected function createSessionQuery(): string
    {
        return "INSERT INTO $this->table (widget_id, challenge_id, data, expires_at) VALUES (?, ?, ?, ?);";
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
        return "SELECT 1 FROM $this->table WHERE widget_id = ? AND challenge_id = ?;";
    }
}
