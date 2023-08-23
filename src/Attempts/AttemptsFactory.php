<?php

namespace IconCaptcha\Attempts;

use IconCaptcha\Attempts\Drivers\Database\PDOAttempts;
use IconCaptcha\Attempts\Drivers\Database\Query\DefaultQuery;
use IconCaptcha\Attempts\Drivers\SessionAttempts;
use InvalidArgumentException;

class AttemptsFactory
{
    /**
     * Create a new attempts/timeout manager instance based on the configuration.
     *
     * @param mixed $storage The storage container.
     * @param string $driver The feature driver.
     * @param array $options The attempts/timeout options.
     * @param string $ipAddress The IP address of the visitor.
     *
     * @return PDOAttempts|SessionAttempts|AttemptsInterface|mixed The generated attempts/timeout manager instance.
     * @throws InvalidArgumentException If the configuration contains an invalid driver.
     */
    public static function create($storage, string $driver, array $options, string $ipAddress): AttemptsInterface
    {
        if (!isset($driver)) {
            throw new InvalidArgumentException('A driver must be specified.');
        }

        switch ($driver) {
            case 'session':
                return new SessionAttempts($storage, $options);
            case 'mysql':
            case 'pgsql':
            case 'postgres':
            case 'sqlite':
            case 'sqlsrv':
            case 'mssql':
                return new PDOAttempts($storage, new DefaultQuery(), $options, $ipAddress);
            default:
                // If none of the supported drivers are used, check if perhaps a custom driver was passed.
                if (class_exists($driver) && in_array(AttemptsInterface::class, class_implements($driver), true)) {
                    return new $driver($storage, $options, $ipAddress);
                }
                throw new InvalidArgumentException("Unsupported attempts/timeout driver [$driver].");
        }
    }
}
