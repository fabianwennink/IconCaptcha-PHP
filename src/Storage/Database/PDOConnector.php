<?php

namespace IconCaptcha\Storage\Database;

use IconCaptcha\Storage\Database\Exceptions\DatabaseOptionsException;
use IconCaptcha\Storage\StorageInterface;
use PDO;

abstract class PDOConnector implements StorageInterface
{
    private const DEFAULT_PDO_OPTIONS = [
        PDO::ATTR_CASE => PDO::CASE_NATURAL,
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ];

    private PDO $connection;

    private array $options;

    public function __construct(array $options)
    {
        $this->options = $options;
    }

    /**
     * @inheritDoc
     * @return PDO
     */
    public function connect(): PDO
    {
        return $this->connection = $this->createConnection();
    }

    /**
     * Establish a database connection. If an existing connection already exists,
     * this connection will be reused instead of establishing a new one.
     */
    private function createConnection(): PDO
    {
        // If an open database connection already exists, reuse it.
        if (!empty($this->connection)) {
            return $this->connection;
        }

        $config = $this->options['connection'];

        // In case the config is an existing PDO object, simply return it.
        if ($config instanceof PDO) {
            $this->connection = $config;
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
     * @param array $config
     * @return array
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
