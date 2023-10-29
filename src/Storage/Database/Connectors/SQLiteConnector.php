<?php

/*
 * IconCaptcha - Copyright 2023, Fabian Wennink (https://www.fabianwennink.nl)
 * Licensed under the MIT license: https://www.fabianwennink.nl/projects/IconCaptcha/license
 *
 * The above copyright notice and license shall be included in all copies or substantial portions of the software.
 */

namespace IconCaptcha\Storage\Database\Connectors;

use IconCaptcha\Exceptions\FileNotFoundException;

class SQLiteConnector extends PDOConnector
{
    /**
     * @inheritDoc
     * @throws FileNotFoundException
     */
    protected function createDsnString(array $config): string
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
}
