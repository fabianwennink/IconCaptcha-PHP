<?php

namespace IconCaptcha\Storage\Database;

class PostgresConnector extends PDOConnector
{
    /**
     * @inheritDoc
     */
    protected function createDsnString(array $config): string
    {
        $dsn = "pgsql:host={$config['host']};dbname={$config['database']}";

        // If a port was specified, add it to the DSN string.
        if (isset($config['port'])) {
            $dsn .= ";port={$config['port']}";
        }

        return $dsn;
    }
}
