<?php

namespace IconCaptcha\Storage\Database;

class MySqlConnector extends PDOConnector
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
}
