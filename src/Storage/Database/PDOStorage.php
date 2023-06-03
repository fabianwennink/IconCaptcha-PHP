<?php

namespace IconCaptcha\Storage\Database;

use PDO;

class PDOStorage implements PDOStorageInterface
{
    private PDO $connection;

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
