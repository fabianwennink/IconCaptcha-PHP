<?php

/*
 * IconCaptcha - Copyright 2023, Fabian Wennink (https://www.fabianwennink.nl)
 * Licensed under the MIT license: https://www.fabianwennink.nl/projects/IconCaptcha/license
 *
 * The above copyright notice and license shall be included in all copies or substantial portions of the software.
 */

namespace IconCaptcha\Session;

use IconCaptcha\Session\Drivers\Database\PDOSession;
use IconCaptcha\Session\Drivers\Database\Query\DefaultQuery;
use IconCaptcha\Session\Drivers\Database\Query\SqlServerQuery;
use IconCaptcha\Session\Drivers\KeyValueSession;
use InvalidArgumentException;

class SessionFactory
{
    /**
     * Creates a new session instance based on the configuration.
     *
     * @param mixed $storage The storage container.
     * @param string $driver The session driver.
     * @param array $options The captcha session options.
     * @param string $ipAddress The IP address of the visitor.
     * @param string $widgetId The widget unique identifier.
     * @param string|null $challengeId The challenge unique identifier.
     * @return PDOSession|KeyValueSession|SessionInterface|mixed The generated session instance.
     * @throws InvalidArgumentException If the configuration contains an invalid driver.
     */
    public static function create($storage, string $driver, array $options, string $ipAddress, string $widgetId, string $challengeId = null): SessionInterface
    {
        if (!isset($driver)) {
            throw new InvalidArgumentException('A session driver must be specified.');
        }

        switch ($driver) {
            case 'session':
                return new KeyValueSession($storage, $options, $ipAddress, $widgetId, $challengeId);
            case 'mysql':
            case 'pgsql':
            case 'postgres':
            case 'sqlite':
                // All included database drivers, except for SQL Server have the same query syntax.
                return new PDOSession($storage, new DefaultQuery(), $options, $ipAddress, $widgetId, $challengeId);
            case 'sqlsrv':
            case 'mssql':
                return new PDOSession($storage, new SqlServerQuery(), $options, $ipAddress, $widgetId, $challengeId);
            default:
                // If none of the supported drivers are used, check if perhaps a custom session driver was passed.
                if (class_exists($driver) && in_array(SessionInterface::class, class_implements($driver), true)) {
                    return new $driver($storage, $options, $ipAddress, $widgetId, $challengeId);
                }
                throw new InvalidArgumentException("Unsupported session driver [$driver].");
        }
    }
}
