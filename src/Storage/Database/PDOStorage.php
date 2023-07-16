<?php

namespace IconCaptcha\Storage\Database;

use PDO;

class PDOStorage implements PDOStorageInterface
{
    /**
     * @var PDO The PDO connection object.
     */
    private PDO $connection;

    /**
     * @var string The format to be used when transforming time into a datetime string.
     */
    private string $datetimeFormat;

    public function __construct(PDO $connection, string $datetimeFormat)
    {
        $this->connection = $connection;
        $this->datetimeFormat = $datetimeFormat;
    }

    /**
     * @inheritDoc
     */
    public function getConnection(): PDO
    {
        return $this->connection;
    }

    /**
     * @inheritDoc
     */
    public function getDatetime(): string
    {
        return $this->formatTimestampAsDatetime(time());
    }

    /**
     * @inheritDoc
     */
    public function formatTimestampAsDatetime(int $timestamp): string
    {
        return date($this->datetimeFormat, $timestamp);
    }
}
