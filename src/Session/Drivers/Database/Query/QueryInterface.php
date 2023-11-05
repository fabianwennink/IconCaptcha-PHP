<?php

/*
 * IconCaptcha - Copyright 2023, Fabian Wennink (https://www.fabianwennink.nl)
 * Licensed under the MIT license: https://www.fabianwennink.nl/projects/IconCaptcha/license
 *
 * The above copyright notice and license shall be included in all copies or substantial portions of the software.
 */

namespace IconCaptcha\Session\Drivers\Database\Query;

interface QueryInterface
{
    /**
     * Returns the database query to fetch a single session.
     *
     * The query is expected to fetch a single record, and select the 'data' and 'expires_at' columns, as
     * well as supply binding placeholders for the 'widget_id' and 'challenge_id' in the WHERE condition.
     *
     * @example
     * SELECT statement: 'data, expires_at'
     * WHERE condition with bindings: 'widget_id = ? AND challenge_id = ?'
     */
    public function loadQuery(string $table): string;

    /**
     * Returns the database query to update an existing session.
     *
     * The query is expected to supply binding placeholders for the following columns, in
     * this exact order: data, expires_at, widget_id, challenge_id
     *
     * The binding placeholders for the 'widget_id' and 'challenge_id' columns are expected to be part of
     * the WHERE condition, whereas the 'data' and 'expires_at' placeholders are part of the UPDATE statement.
     *
     * @example
     * SET statement with bindings: 'data = ?, expires_at = ?'
     * WHERE condition with bindings: 'widget_id = ? AND challenge_id = ?'
     */
    public function saveQuery(string $table): string;

    /**
     * Returns the database query to insert a new session record.
     *
     * The query is expected to supply binding placeholders for the following columns, in
     * this exact order: widget_id, challenge_id, data, expires_at
     *
     * @example VALUES statement with bindings: '(?, ?, ?, ?)'
     */
    public function createQuery(string $table): string;

    /**
     * Returns the database query to delete a session record.
     *
     * The query is expected to supply binding placeholders for the 'widget_id' and 'challenge_id'.
     * These should be used in the WHERE condition to ensure the correct record will be deleted.
     *
     * @example WHERE condition with bindings: 'widget_id = ? AND challenge_id = ?'
     */
    public function destroyQuery(string $table): string;

    /**
     * Returns the database query to delete expired sessions.
     *
     * The query is expected to supply a binding placeholder for the 'expires_at' column.
     * This column should be used in the WHERE condition to ensure the correct records will be deleted.
     *
     * @example WHERE condition with binding: 'expires_at < ?'
     */
    public function purgeQuery(string $table): string;

    /**
     * Returns the database query to check if a session exists.
     *
     * The query is expected to request only 1 record, and supply binding placeholders for the 'widget_id' and 'challenge_id' columns.
     * These should be used in the WHERE condition to ensure that the existence of the correct session is checked.
     *
     * @example WHERE condition with bindings: 'widget_id = ? AND challenge_id = ?'
     */
    public function existsQuery(string $table): string;
}
