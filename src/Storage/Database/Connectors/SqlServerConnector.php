<?php

/*
 * IconCaptcha - Copyright 2023, Fabian Wennink (https://www.fabianwennink.nl)
 * Licensed under the MIT license: https://www.fabianwennink.nl/projects/IconCaptcha/license
 *
 * The above copyright notice and license shall be included in all copies or substantial portions of the software.
 */

namespace IconCaptcha\Storage\Database\Connectors;

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
