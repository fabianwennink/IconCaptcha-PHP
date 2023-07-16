<?php

namespace IconCaptcha\Storage\Database;

use PDO;

interface PDOStorageInterface
{
    /**
     * Returns the PDO connection.
     */
    public function getConnection(): PDO;

    /**
     * Returns the current time as a formatted datetime string.
     */
    public function getDatetime(): string;

    /**
     * Formats the given timestamp into a database supported datetime string.
     *
     * @param int $timestamp The timestamp to format.
     */
    public function formatTimestampAsDatetime(int $timestamp): string;
}
