<?php

/*
 * IconCaptcha - Copyright 2023, Fabian Wennink (https://www.fabianwennink.nl)
 * Licensed under the MIT license: https://www.fabianwennink.nl/projects/IconCaptcha/license
 *
 * The above copyright notice and license shall be included in all copies or substantial portions of the software.
 */

namespace IconCaptcha\Attempts\Drivers\Database\Query;

class DefaultQuery implements QueryInterface
{
    /**
     * @inheritDoc
     */
    public function insertAttemptQuery(string $table): string
    {
        return "INSERT INTO $table (ip_address, attempts, valid_until) VALUES (?, ?, ?);";
    }

    /**
     * @inheritDoc
     */
    public function increaseAttemptsQuery(string $table): string
    {
        return "UPDATE $table SET attempts = ?, valid_until = ? WHERE ip_address = ?;";
    }

    /**
     * @inheritDoc
     */
    public function clearAttemptsQuery(string $table): string
    {
        return "DELETE FROM $table WHERE ip_address = ?;";
    }

    /**
     * @inheritDoc
     */
    public function issueTimeoutQuery(string $table): string
    {
        return "UPDATE $table SET attempts = 0, timeout_until = ?, valid_until = ? WHERE ip_address = ?;";
    }

    /**
     * @inheritDoc
     */
    public function activeTimeoutTimestampQuery(string $table): string
    {
        return "SELECT timeout_until FROM $table WHERE ip_address = ? AND valid_until >= ?;";
    }

    /**
     * @inheritDoc
     */
    public function currentAttemptsCountQuery(string $table): string
    {
        return "SELECT attempts FROM $table WHERE ip_address = ? AND valid_until >= ?;";
    }

    /**
     * @inheritDoc
     */
    public function attemptsValidityTimestampQuery(string $table): string
    {
        return "SELECT valid_until FROM $table WHERE ip_address = ?;";
    }

    /**
     * @inheritDoc
     */
    public function purgeExpiredTimeoutsQuery(string $table): string
    {
        return "DELETE FROM $table WHERE valid_until < ?;";
    }
}
