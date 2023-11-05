<?php

/*
 * IconCaptcha - Copyright 2023, Fabian Wennink (https://www.fabianwennink.nl)
 * Licensed under the MIT license: https://www.fabianwennink.nl/projects/IconCaptcha/license
 *
 * The above copyright notice and license shall be included in all copies or substantial portions of the software.
 */

namespace IconCaptcha\Storage;

use IconCaptcha\Storage\Database\Connectors\MySqlConnector;
use IconCaptcha\Storage\Database\Connectors\PostgresConnector;
use IconCaptcha\Storage\Database\Connectors\SQLiteConnector;
use IconCaptcha\Storage\Database\Connectors\SqlServerConnector;
use IconCaptcha\Storage\Session\SessionConnector;
use InvalidArgumentException;

class StorageFactory
{
    /**
     * Creates a new storage instance based on the configured storage driver.
     *
     * @param array $options The captcha storage options.
     * @return StorageConnectorInterface The generated storage instance.
     * @throws InvalidArgumentException If the configuration contains an invalid driver.
     */
    public static function create(array $options): StorageConnectorInterface
    {
        $driver = $options['driver'];

        if (!isset($driver)) {
            throw new InvalidArgumentException('A storage driver must be specified.');
        }

        switch ($driver) {
            case 'session':
                return new SessionConnector();
            case 'mysql':
                return new MySqlConnector($options);
            case 'pgsql':
            case 'postgres':
                return new PostgresConnector($options);
            case 'sqlite':
                return new SQLiteConnector($options);
            case 'sqlsrv':
            case 'mssql':
                return new SqlServerConnector($options);
            default:
                // If none of the supported drivers are used, check if perhaps a custom storage driver was passed.
                if (class_exists($driver) && in_array(StorageConnectorInterface::class, class_implements($driver), true)) {
                    return new $driver($options);
                }
                throw new InvalidArgumentException("Unsupported storage driver [$driver].");
        }
    }
}
