<?php

namespace IconCaptcha\Storage\Database;

use PDO;

class SqlServerConnector extends PDOConnector
{
    /**
     * @inheritDoc
     */
    protected function createDsnString(array $config): string
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
}
