<?php

/*
 * IconCaptcha - Copyright 2023, Fabian Wennink (https://www.fabianwennink.nl)
 * Licensed under the MIT license: https://www.fabianwennink.nl/projects/IconCaptcha/license
 *
 * The above copyright notice and license shall be included in all copies or substantial portions of the software.
 */

namespace IconCaptcha\Storage\Session;

interface SessionStorageInterface
{
    /**
     * Reads a value from the session storage.
     *
     * @param string $key The key of the value to read. Dot notation is supported.
     * @return mixed|null The value if it exists, or null if it does not exist.
     */
    public function read(string $key);

    /**
     * Writes a value to the session storage.
     *
     * @param string $key The key to write the value to. Dot notation is supported.
     * @param mixed $value The value to write to the session.
     */
    public function write(string $key, $value): void;

    /**
     * Removes a value from the session storage.
     *
     * @param string $key The key of the value to remove. Dot notation is supported.
     */
    public function remove(string $key): void;

    /**
     * Checks if a value exists in the session storage.
     *
     * @param string $key The key to check for. Dot notation is supported.
     */
    public function exists(string $key): bool;
}
