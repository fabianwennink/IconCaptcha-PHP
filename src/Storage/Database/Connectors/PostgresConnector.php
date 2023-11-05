<?php

/*
 * IconCaptcha - Copyright 2023, Fabian Wennink (https://www.fabianwennink.nl)
 * Licensed under the MIT license: https://www.fabianwennink.nl/projects/IconCaptcha/license
 *
 * The above copyright notice and license shall be included in all copies or substantial portions of the software.
 */

namespace IconCaptcha\Storage\Database\Connectors;

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
