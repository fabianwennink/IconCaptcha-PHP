<?php

namespace IconCaptcha\Storage\Session;

class SessionStorage
{
    /**
     * @var string The session name.
     */
    private string $sessionName;

    public function __construct(string $sessionName)
    {
        $this->sessionName = $sessionName;
    }

    /**
     * Reads a value from the session storage.
     * @param string $key The key of the value to read. Dot notation is supported.
     * @return mixed|null The value if it exists, or null if it does not exist.
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
     * Writes a value to the session storage.
     * @param string $key The key to write the value to. Dot notation is supported.
     * @param mixed $value The value to write to the session.
     */
    public function write(string $key, $value): void
    {
        $segments = explode('.', $key);
        $data = &$this->getDataBySegments($segments);
        $data = $value;
    }

    /**
     * Removes a value from the session storage.
     * @param string $key The key of the value to remove. Dot notation is supported.
     */
    public function remove(string $key): void
    {
        $segments = explode('.', $key);
        $data = &$this->getDataBySegments($segments);
        unset($data);
    }

    /**
     * Checks if a value exists in the session storage.
     * @param string $key The key to check for. Dot notation is supported.
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
