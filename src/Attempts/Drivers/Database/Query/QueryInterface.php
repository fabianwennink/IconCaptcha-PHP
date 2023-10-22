<?php

/*
 * IconCaptcha - Copyright 2023, Fabian Wennink (https://www.fabianwennink.nl)
 * Licensed under the MIT license: https://www.fabianwennink.nl/projects/IconCaptcha/license
 *
 * The above copyright notice and license shall be included in all copies or substantial portions of the software.
 */

namespace IconCaptcha\Attempts\Drivers\Database\Query;

interface QueryInterface
{
    /**
     * Returns the database query to insert a new attempt for a given IP address.
     *
     * The query is expected to supply binding placeholders for the following columns, in
     * this exact order: ip_address, attempts, valid_until.
     *
     * @example VALUES statement with bindings: '(?, ?, ?)'
     */
    public function insertAttemptQuery(string $table): string;

    /**
     * Returns the database query to increase the attempts counter for a given IP address.
     *
     * The binding placeholder for the 'ip_address' column is expected to be part of the WHERE
     * condition, whereas the 'attempts' and 'valid_until' placeholders are part of the UPDATE statement.
     *
     * @example
     * SET statement with bindings: 'attempts = ?, valid_until = ?'
     * WHERE condition with bindings: 'ip_address = ?'
     */
    public function increaseAttemptsQuery(string $table): string;

    /**
     * Returns the database query to delete stored attempts and/or an issued timeout beloning to an IP address.
     *
     * The query is expected to supply binding placeholders for the 'ip_address'.
     * These should be used in the WHERE condition to ensure the correct record will be deleted.
     *
     * @example WHERE condition with bindings: 'ip_address = ?'
     */
    public function clearAttemptsQuery(string $table): string;

    /**
     * Returns the database query to issue a timeout for a given IP address.
     *
     * The query is expected to supply binding placeholders for the following columns, in
     * this exact order: ip_address, timeout_until, valid_until. The timeout_until and valid_until
     * columns are expected to receive the exact same timestamp.
     *
     * The binding placeholder for the 'ip_address' column is expected to be part of the WHERE
     * condition, whereas the 'timeout_until' and 'valid_until' placeholders are part of the UPDATE statement.
     *
     * @example
     * SET statement with bindings: 'timeout_until = ?, valid_until = ?'
     * WHERE condition with bindings: 'ip_address = ?'
     */
    public function issueTimeoutQuery(string $table): string;

    /**
     * Returns the database query to retrieve an active timeout expiration timestamp beloning to an IP address.
     *
     * The query is expected to supply a binding placeholder for the 'ip_address' and 'valid_until' columns.
     * These columns should be used in the WHERE condition to ensure the correct record will be fetched.
     *
     * @example WHERE condition with binding: 'ip_address = ? AND valid_until >= ?'
     */
    public function activeTimeoutTimestampQuery(string $table): string;

    /**
     * Returns the database query to retrieve the current attempts count beloning to an IP address.
     *
     * The query is expected to supply a binding placeholder for the 'ip_address' and 'valid_until' columns.
     * These columns should be used in the WHERE condition to ensure the correct record will be fetched.
     *
     * @example WHERE condition with binding: 'ip_address = ? AND valid_until >= ?'
     */
    public function currentAttemptsCountQuery(string $table): string;

    /**
     * Returns the database query to retrieve the validity timestamp of the attempts/timeout record belonging to an IP address.
     *
     * The query is expected to supply a binding placeholder for the 'valid_until' column.
     * This column should be used in the WHERE condition to ensure the correct record will be fetched.
     *
     * @example WHERE condition with binding: 'valid_until < ?'
     */
    public function attemptsValidityTimestampQuery(string $table): string;

    /**
     * Returns the database query to delete expired attempts and/or issued timeouts.
     *
     * The query is expected to supply a binding placeholder for the 'valid_until' column.
     * This column should be used in the WHERE condition to ensure the correct records will be deleted.
     *
     * @example WHERE condition with binding: 'valid_until < ?'
     */
    public function purgeExpiredTimeoutsQuery(string $table): string;
}
