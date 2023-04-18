<?php

namespace IconCaptcha\Storage\Database;

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
