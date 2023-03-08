<?php

/**
 * IconCaptcha Plugin: v3.1.1
 * Copyright © 2022, Fabian Wennink (https://www.fabianwennink.nl)
 *
 * Licensed under the MIT license: https://www.fabianwennink.nl/projects/IconCaptcha/license
 */

namespace IconCaptcha\Session\Drivers\Database;

class MySqlSession extends PDOSession
{
    /**
     * @inheritDoc
     */
    protected function createDsnString(array $config): string
    {
        $dsn = "mysql:host={$config['host']};dbname={$config['database']}";

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
        return "SELECT `data`, `expires_at` FROM $this->table WHERE `widget_id` = ? AND `challenge_id` = ? LIMIT 1";
    }

    /**
     * @inheritDoc
     */
    protected function saveSessionQuery(): string
    {
        return "UPDATE $this->table SET `data` = ?, `expires_at` = ? WHERE `widget_id` = ? AND `challenge_id` = ?;";
    }

    /**
     * @inheritDoc
     */
    protected function createSessionQuery(): string
    {
        return "INSERT INTO $this->table (`widget_id`, `challenge_id`, `data`, `expires_at`) VALUES (?, ?, ?, ?);";
    }

    /**
     * @inheritDoc
     */
    protected function destroySessionQuery(): string
    {
        return "DELETE FROM $this->table WHERE `widget_id` = ? AND `challenge_id` = ?;";
    }

    /**
     * @inheritDoc
     */
    protected function purgeExpiredSessionsQuery(): string
    {
        return "DELETE FROM $this->table WHERE `expires_at` IS NOT NULL AND `expires_at` < ?;";
    }

    /**
     * @inheritDoc
     */
    protected function sessionExistsQuery(): string
    {
        return "SELECT 1 FROM $this->table WHERE `widget_id` = ? AND `challenge_id` = ? LIMIT 1;";
    }
}
