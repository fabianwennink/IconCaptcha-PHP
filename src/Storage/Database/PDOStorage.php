<?php

/*
 * IconCaptcha - Copyright 2023, Fabian Wennink (https://www.fabianwennink.nl)
 * Licensed under the MIT license: https://www.fabianwennink.nl/projects/IconCaptcha/license
 *
 * The above copyright notice and license shall be included in all copies or substantial portions of the software.
 */

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

    /**
     * Creates a new database (PRO) storage instance.
     *
     * @param PDO $connection The connection to the database.
     * @param string $datetimeFormat The format to be used when transforming time into a datetime string.
     */
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
