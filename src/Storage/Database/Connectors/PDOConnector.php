<?php

/*
 * IconCaptcha - Copyright 2023, Fabian Wennink (https://www.fabianwennink.nl)
 * Licensed under the MIT license: https://www.fabianwennink.nl/projects/IconCaptcha/license
 *
 * The above copyright notice and license shall be included in all copies or substantial portions of the software.
 */

namespace IconCaptcha\Storage\Database\Connectors;

use IconCaptcha\Storage\Database\Exceptions\DatabaseOptionsException;
use IconCaptcha\Storage\Database\PDOStorage;
use IconCaptcha\Storage\StorageConnectorInterface;
use PDO;

abstract class PDOConnector implements StorageConnectorInterface
{
    /**
     * List of default PDO connection options.
     */
    private const DEFAULT_PDO_OPTIONS = [
        PDO::ATTR_CASE => PDO::CASE_NATURAL,
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ];

    /**
     * @var array The database storage options.
     */
    private array $options;

    /**
     * Creates a new PDO connector instance.
     *
     * @param array $options The database storage options.
     */
    public function __construct(array $options)
    {
        $this->options = $options;
    }

    /**
     * @inheritDoc
     * @return PDO
     */
    public function connect(): PDOStorage
    {
        return new PDOStorage(
            $this->createConnection(),
            $this->options['datetimeFormat']
        );
    }

    /**
     * Establish a database connection. If an existing connection already exists,
     * this connection will be reused instead of establishing a new one.
     */
    private function createConnection(): PDO
    {
        $config = $this->options['connection'];

        // In case the config is an existing PDO object, simply return it.
        if ($config instanceof PDO) {
            return $config;
        }

        if (is_array($config)) {

            // If a DSN string (URL) is specified, simply return it. At this
            // point assume that the DSN string is a correct, valid DSN
            // string, and that no further adjustments have to be made to it.
            if (!empty($config['url'])) {
                $dsnString = $config['url'];
            } else {
                $dsnString = $this->createDsnString($config);
            }

            return new PDO($dsnString, $config['username'] ?? null, $config['password'] ?? null, $this->getConnectionOptions($config));
        }

        throw new DatabaseOptionsException($config);
    }

    /**
     * Get the connection options for the given configuration. The given
     * configuration options will be merged with the default PDO options.
     *
     * @param array $config The configuration array.
     */
    private function getConnectionOptions(array $config): array
    {
        $options = $config['options'] ?? [];

        return array_diff_key(self::DEFAULT_PDO_OPTIONS, $options) + $options;
    }

    /**
     * Creates a DSN string from connection configuration details.
     */
    abstract protected function createDsnString(array $config): string;
}
