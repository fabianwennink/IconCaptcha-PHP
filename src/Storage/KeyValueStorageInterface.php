<?php

/*
 * IconCaptcha - Copyright 2023, Fabian Wennink (https://www.fabianwennink.nl)
 * Licensed under the MIT license: https://www.fabianwennink.nl/projects/IconCaptcha/license
 *
 * The above copyright notice and license shall be included in all copies or substantial portions of the software.
 */

namespace IconCaptcha\Storage;

interface KeyValueStorageInterface
{
    /**
     * Retrieves a value from the key-value storage.
     *
     * @param string $key The unique key associated with the value.
     * @return mixed|null The stored value if it exists, or NULL if not found.
     */
    public function read(string $key);

    /**
     * Stores a value in the key-value storage.
     *
     * @param string $key The unique key where the value will be stored.
     * @param mixed $value The data to be stored.
     */
    public function write(string $key, $value): void;

    /**
     * Deletes a value from the key-value storage.
     *
     * @param string $key The key of the value to be removed.
     */
    public function remove(string $key): void;

    /**
     * Checks if a value is present in the key-value storage.
     *
     * @param string $key The key to be checked for existence.
     * @return bool TRUE if the value exists, FALSE otherwise.
     */
    public function exists(string $key): bool;
}
