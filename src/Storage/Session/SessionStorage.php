<?php

/*
 * IconCaptcha - Copyright 2023, Fabian Wennink (https://www.fabianwennink.nl)
 * Licensed under the MIT license: https://www.fabianwennink.nl/projects/IconCaptcha/license
 *
 * The above copyright notice and license shall be included in all copies or substantial portions of the software.
 */

namespace IconCaptcha\Storage\Session;

use IconCaptcha\Storage\KeyValueStorageInterface;

class SessionStorage implements KeyValueStorageInterface
{
    /**
     * @var string The session name.
     */
    private string $sessionName;

    /**
     * Creates a new session storage instance.
     *
     * @param string $sessionName The session name.
     */
    public function __construct(string $sessionName)
    {
        $this->sessionName = $sessionName;
    }

    /**
     * @inheritDoc
     */
    public function read(string $key)
    {
        $segments = explode('.', $key);
        $data = $_SESSION[$this->sessionName];

        foreach ($segments as $segment) {
            if (!array_key_exists($segment, $data)) {
                return null;
            }
            $data = $data[$segment];
        }

        return $data;
    }

    /**
     * @inheritDoc
     */
    public function write(string $key, $value): void
    {
        $segments = explode('.', $key);
        $data = &$this->getDataBySegments($segments);
        $data = $value;
    }

    /**
     * @inheritDoc
     */
    public function remove(string $key): void
    {
        $segments = explode('.', $key);
        $lastSegment = array_pop($segments);
        $data = &$this->getDataBySegments($segments);
        unset($data[$lastSegment]);
    }

    /**
     * @inheritDoc
     */
    public function exists(string $key): bool
    {
        $segments = explode('.', $key);
        $data = $_SESSION[$this->sessionName];

        foreach ($segments as $segment) {
            if (!array_key_exists($segment, $data)) {
                return false;
            }
            $data = $data[$segment];
        }

        return true;
    }

    private function &getDataBySegments(array $segments): array
    {
        $data = &$_SESSION[$this->sessionName];

        foreach ($segments as $segment) {
            if (!isset($data[$segment]) || !is_array($data[$segment])) {
                $data[$segment] = [];
            }
            $data = &$data[$segment];
        }

        return $data;
    }
}
