<?php

namespace IconCaptcha\Storage;

use IconCaptcha\Storage\Database\MySqlConnector;
use IconCaptcha\Storage\Database\PostgresConnector;
use IconCaptcha\Storage\Database\SQLiteConnector;
use IconCaptcha\Storage\Database\SqlServerConnector;
use IconCaptcha\Storage\Session\SessionConnector;
use InvalidArgumentException;

class StorageFactory
{
    /**
     * Create a new storage instance based on the driver configuration.
     *
     * @param array $options The captcha storage options.
     *
     * @return StorageInterface The generated storage instance.
     * @throws InvalidArgumentException If the configuration contains an invalid driver.
     */
    public static function create(array $options): StorageInterface
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
                if (class_exists($driver) && in_array(StorageInterface::class, class_implements($driver), true)) {
                    return new $driver($options);
                }
                throw new InvalidArgumentException("Unsupported storage driver [$driver].");
        }
    }
}
