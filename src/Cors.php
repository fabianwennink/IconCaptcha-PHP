<?php

/*
 * IconCaptcha - Copyright 2023, Fabian Wennink (https://www.fabianwennink.nl)
 * Licensed under the MIT license: https://www.fabianwennink.nl/projects/IconCaptcha/license
 *
 * The above copyright notice and license shall be included in all copies or substantial portions of the software.
 */

namespace IconCaptcha;

class Cors
{
    /**
     * @var array All allowed origins.
     */
    private array $origins;

    /**
     * @var array All allowed wildcard origins.
     */
    private array $wildcardOrigins = [];

    /**
     * @var bool Whether to expose the credentials (cookies, authorization headers, or TLS client certificates) in the response.
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Access-Control-Allow-Credentials
     */
    private bool $allowCredentials;

    /**
     * @var bool Whether all origins should be allowed.
     */
    private bool $allowAllOrigins;

    /**
     * @var int For how long the CORS response should be cached, in seconds.
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Access-Control-Max-Age
     */
    private int $cacheAge;

    /**
     * @var string[] List of set Vary headers.
     * https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Vary
     */
    private array $vary = [];

    /**
     * Creates a new CORS handler instance.
     *
     * @param array $origins All allowed origins.
     * @param bool $allowCredentials Whether to expose the credentials in the response.
     * @param int $cacheAge For how long the CORS response should be cached, in seconds.
     */
    public function __construct(array $origins, bool $allowCredentials, int $cacheAge)
    {
        $this->origins = $origins;
        $this->allowAllOrigins = empty($this->origins) || in_array('*', $this->origins, true);
        $this->allowCredentials = $allowCredentials;
        $this->cacheAge = $cacheAge;

        // Store all request Vary header values.
        if (isset($_SERVER['HTTP_VARY'])) {
            $this->vary = explode(',', str_replace(' ', '', $_SERVER['HTTP_VARY']));
        }

        // Prepare the wildcard URL patterns.
        if (!empty($this->origins)) {
            foreach ($this->origins as $origin) {
                if (strpos($origin, '*') !== false) {
                    $this->wildcardOrigins[] = $this->convertWildcardUrl($origin);
                }
            }
        }
    }

    /**
     * Handles the CORS request.
     */
    public function handleCors(): void
    {
        $originAllowed = $this->addOriginHeader();

        if ($originAllowed && $this->allowCredentials) {
            header('Access-Control-Allow-Credentials: true');
        }

        // Process preflight check.
        if ($this->isPreflightRequest()) {

            header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
            $this->vary[] = 'Access-Control-Allow-Methods';

            if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'])) {
                header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");
                $this->vary[] = 'Access-Control-Allow-Headers';
            }

            // Cache the preflight request.
            if ($this->cacheAge > 0) {
                header("Access-Control-Max-Age: $this->cacheAge");
            }

            // Apply Vary header for preflight request.
            $this->applyVaryHeaders($this->vary);

            // Exit script with status 204.
            http_response_code(204);
            exit(0);
        }

        // Apply Vary header for CORS requests.
        $this->applyVaryHeaders($this->vary);
    }

    /**
     * Will set the 'Access-Control-Allow-Origin' header in case the request
     * origin is allowed. In case no specific origin is configured, all origins
     * will be allowed (set as *).
     *
     * @return bool TRUE if the header was set, FALSE if it was not set.
     */
    private function addOriginHeader(): bool
    {
        if ($this->allowAllOrigins) {
            header("Access-Control-Allow-Origin: *");
            return true;
        }

        if ($this->isCorsRequest()) {
            $origin = $_SERVER['HTTP_ORIGIN'];

            // Only set the 'allow origin' header if the origin is actually allowed.
            if ($this->isOriginAllowed($origin)) {
                header("Access-Control-Allow-Origin: $origin");
                return true;
            }

            $this->vary[] = 'Origin';
        }

        return false;
    }

    /**
     * Checks if the given origin is allowed to make a request.
     *
     * @param string $origin The origin URL.
     */
    private function isOriginAllowed(string $origin): bool
    {
        // Simple origin check.
        if (in_array($origin, $this->origins, true)) {
            return true;
        }

        // Check the origin against the wildcard patterns.
        foreach ($this->wildcardOrigins as $pattern) {
            if (preg_match($pattern, $origin)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns whether the request is a CORS request.
     */
    private function isCorsRequest(): bool
    {
        return isset($_SERVER['HTTP_ORIGIN']);
    }

    /**
     * Returns whether the request is a preflight request.
     */
    private function isPreflightRequest(): bool
    {
        return $_SERVER['REQUEST_METHOD'] === 'OPTIONS' && $_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'];
    }

    /**
     * Converts the given wildcard URL and returns a regex pattern for easier comparison.
     *
     * @param string $url The wildcard URL.
     * @return string The wildcard URL regex pattern.
     */
    private function convertWildcardUrl(string $url): string
    {
        $url = preg_quote($url, '#');
        $url = str_replace('\*', '.*', $url);
        return "#^$url\z#u";
    }

    /**
     * Will set the 'Vary' header with the given values.
     *
     * @param array $headers The header names to set in the Vary header.
     */
    private function applyVaryHeaders(array $headers): void
    {
        if (!empty($headers)) {
            $combinedValue = implode(', ', array_unique($headers));
            header("Vary: $combinedValue");
        }
    }
}
