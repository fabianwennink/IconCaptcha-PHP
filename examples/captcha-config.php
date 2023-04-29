<?php

/**
 * Configuration options for IconCaptcha library.
 *
 * This array contains various configuration options that can be used to customize
 * the behavior of the IconCaptcha library. All options are optional and have default values.
 *
 * For more information on each option, please refer to the documentation on the GitHub wiki:
 * - https://github.com/fabianwennink/IconCaptcha-Plugin-jQuery-PHP/wiki
 */
return [
    // Specifies the directory path where the icon files are located.
    'iconPath' => __DIR__ . '/../assets/icons/',

    // Specifies a function that must return the IP address of the visitor.
    // Using Cloudflare? Ensure to return the visitor's original IP (HTTP_CF_CONNECTING_IP) and not the proxy IP.
    'ipAddress' => static fn() => $_SERVER['REMOTE_ADDR'],

    // Configurations for additional custom themes.
    'themes' => [
        'black' => [
            // Specifies which icon type should be used: light or dark.
            'iconStyle' => 'light',
            // Specifies the RGB color of the icon separator.
            'separatorColor' => [20, 20, 20],
        ]
    ],

    // Configuration for the challenge generation.
    'challenge' => [
        // Specifies the maximum number of unique icons available. By default, IconCaptcha ships with 180 icons.
        'availableIcons' => 180,
        // Specifies the minimum and maximum number of icons to use in each challenge image.
        'iconAmount' => [
            'min' => 5, // The lowest possible is 5 icons per challenge.
            'max' => 8 // The highest possible is 8 icons per challenge.
        ],
        // Specifies whether to randomly rotate the icons in each challenge image.
        'rotate' => true,
        // Specifies whether to randomly flip the icons in each challenge image, horizontally and/or vertically.
        'flip' => [
            'horizontally' => true,
            'vertically' => true,
        ],
        // Specifies whether to render a border between the icons in each challenge image.
        'border' => true,
        // Specifies the generator class to use for creating challenge images. Generators for 'GD' and 'Imagick' extensions are available.
        'generator' => \IconCaptcha\Challenge\Generators\GD::class,
    ],

    // Configuration for challenge validation.
    'validation' => [
        // Specifies the duration (in seconds) of inactivity before a challenge is invalidated. Set to 0 to disable.
        'inactivityExpiration' => 120,
        // Specifies the duration (in seconds) after a successful challenge before it's invalidated. Set to 0 to disable.
        'completionExpiration' => 300,
        // Specifies whether to include the request latency when generating expiration timestamps.
        'latencyCorrection' => true,
        // Specifies the options for challenge solving attempts.
        'attempts' => [
            // Specifies whether to enable the attempts and timeout feature.
            'enabled' => true,
            // Specifies the maximum number of attempts before the visitor will receive a timeout.
            'amount' => 2,
            // Specifies the time (in seconds) which the visitor has to wait after making too many incorrect attempts.
            'timeout' => 60,
            // Specifies the time (in seconds) after which an attempt will automatically be forgotten and removed from the attempts counter.
            'valid' => 30,
            // Specifies the driver to use for storing and retrieving attempts and timeout data.
            // Default available drivers: 'session'
            'driver' => 'session'
        ],
    ],

    // Configuration for the session driver.
    'session' => [
        // Specifies the session driver to use for storing and retrieving challenge data.
        // Default available drivers: 'session', 'mysql', 'sqlsrv', 'pgsql' and 'sqlite'.
        'driver' => 'session',
        // Specifies the options passed on to the session driver.
        'options' => [
            // Specifies the connection details for database session driver.
            // Alternatively, you can use an existing PDO object for the 'connection' key.
            'connection' => [
                //'url' => 'mysql:host=127.0.0.1;port=3306;dbname=db', // You can use a DSN URL if your database requires a more complex connection.
                'host' => '127.0.0.1',
                'port' => 3306,
                'database' => 'db',
                'username' => 'root',
                'password' => '',
                'table' => 'iconcaptcha_challenges',
            ],
        ],
    ],

    // Configuration for Cross-Origin Resource Sharing (CORS).
    'cors' => [
        // Specifies whether CORS is enabled.
        'enabled' => false,
        // Specifies the list of allowed origins for CORS requests.
        // Wildcards, such as *.example.com, are supported. Use '*' to allow all origins, but be aware of the potential security implications.
        'origins' => [],
        // Specifies whether to include credentials (cookies, headers, etc.) in CORS requests.
        'credentials' => true,
        // Specifies the maximum age (in seconds) to cache CORS preflight requests.
        'cache' => 86400,
    ],

    // Specifies the token class to use for challenge CSRF tokens. Set to null to disable.
    // The default token class is \IconCaptcha\Token\Token::class.
    'token' => null,

    // Configuration for hooks.
    'hooks' => [
        // Initialization hook, called when the challenge is requested.
        // Use case: To determine whether to serve a challenge, or complete immediately, e.g. based on IP or previously completed challenges.
        // Required: The hook must implement the 'InitHookInterface' interface.
        'init' => null,
        // Image generation hook, called after the challenge image was generated.
        // Example use case: Modify the image by applying filters or adding random noise to increase the difficulty.
        // Required: The hook must implement the 'GenerationHookInterface' interface.
        'generation' => null,
        // User image interaction hook, called after the user clicked on an icon.
        // Example use case: Perform a custom action based on whether the user made a correct or incorrect choice.
        // Required: The hook must implement the 'SelectionHookInterface' interface.
        'selection' => null,
    ],
];
