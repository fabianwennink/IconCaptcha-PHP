<?php

/*
 * IconCaptcha - Copyright 2023, Fabian Wennink (https://www.fabianwennink.nl)
 * Licensed under the MIT license: https://www.fabianwennink.nl/projects/IconCaptcha/license
 *
 * The above copyright notice and license shall be included in all copies or substantial portions of the software.
 */

namespace IconCaptcha\Session\Drivers\Database\Query;

class DefaultQuery implements QueryInterface
{
    /**
     * @inheritDoc
     */
    public function loadQuery(string $table): string
    {
        return "SELECT puzzle, expires_at FROM $table WHERE widget_id = ? AND challenge_id = ? LIMIT 1;";
    }

    /**
     * @inheritDoc
     */
    public function saveQuery(string $table): string
    {
        return "UPDATE $table SET puzzle = ?, expires_at = ? WHERE widget_id = ? AND challenge_id = ?;";
    }

    /**
     * @inheritDoc
     */
    public function createQuery(string $table): string
    {
        return "INSERT INTO $table (widget_id, challenge_id, puzzle, expires_at, ip_address) VALUES (?, ?, ?, ?, ?);";
    }

    /**
     * @inheritDoc
     */
    public function destroyQuery(string $table): string
    {
        return "DELETE FROM $table WHERE widget_id = ? AND challenge_id = ?;";
    }

    /**
     * @inheritDoc
     */
    public function purgeQuery(string $table): string
    {
        return "DELETE FROM $table WHERE expires_at IS NOT NULL AND expires_at < ?;";
    }

    /**
     * @inheritDoc
     */
    public function existsQuery(string $table): string
    {
        return "SELECT 1 FROM $table WHERE widget_id = ? AND challenge_id = ? LIMIT 1;";
    }
}
