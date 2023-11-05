<?php

/*
 * IconCaptcha - Copyright 2023, Fabian Wennink (https://www.fabianwennink.nl)
 * Licensed under the MIT license: https://www.fabianwennink.nl/projects/IconCaptcha/license
 *
 * The above copyright notice and license shall be included in all copies or substantial portions of the software.
 */

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
