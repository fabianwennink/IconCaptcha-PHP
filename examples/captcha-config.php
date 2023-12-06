<?php

/**
 * Configuration options for IconCaptcha library.
 *
 * This array contains various configuration options that can be used to customize
 * the behavior of the IconCaptcha library. All options are optional and have default values.
 *
 * For more information on each option, please refer to the documentation: https://github.com/fabianwennink/IconCaptcha-PHP/wiki
 */
return [
    // Specifies the directory path where the icon files are located.
    // When using Composer, you should set this to null, except if you've published the assets to your own project during setup.
    // For more information, see https://github.com/fabianwennink/IconCaptcha-PHP/wiki/Configuration#iconpath
    'iconPath' => __DIR__ . '/../assets/icons/',

    // Specifies a function that must return the IP address of the visitor.
    // Using Cloudflare? Ensure to return the visitor's original IP (HTTP_CF_CONNECTING_IP) and not the proxy IP.
    // For more information, see https://github.com/fabianwennink/IconCaptcha-PHP/wiki/Configuration#ipaddress
    'ipAddress' => static fn() => $_SERVER['REMOTE_ADDR'],

    // Specifies the token class to use for challenge CSRF tokens. Set to null to disable.
    // The default token class is \IconCaptcha\Token\IconCaptchaToken::class.
    // For more information, see https://github.com/fabianwennink/IconCaptcha-PHP/wiki/Token
    'token' => \IconCaptcha\Token\IconCaptchaToken::class,

    // Configurations for additional custom themes.
    // For more information, see https://github.com/fabianwennink/IconCaptcha-PHP/wiki/Themes
    'themes' => [
        'black' => [
            // Specifies which icon type should be used: light or dark.
            'iconStyle' => 'light',
            // Specifies the RGB color of the icon separator.
            'separatorColor' => [20, 20, 20],
        ]
    ],

    // Configuration for database storage.
    'storage' => [
        // Specifies the driver to use for data storage.
        // Default available drivers: 'session', 'mysql', 'sqlsrv', 'pgsql' and 'sqlite'.
        'driver' => 'session',
        // Specifies the connection details for database session driver.
        // Alternatively, you can use an existing PDO object for the 'connection' key.
        // For more information, see https://github.com/fabianwennink/IconCaptcha-PHP/wiki/Database-Storage
        'connection' => [
            //'url' => 'mysql:host=127.0.0.1;port=3306;dbname=db', // You can use a DSN URL if your database requires a more complex connection.
            'host' => '127.0.0.1',
            'port' => 3306,
            'database' => 'db',
            'username' => 'root',
            'password' => '',
        ],
        // Specifies the format used to create formatted datetime values to use in database queries.
        // Use a different format if needed for your database. See https://www.php.net/manual/en/datetime.format.php.
        'datetimeFormat' => 'Y-m-d H:i:s',
    ],

    // Configuration for the challenge generation.
    'challenge' => [
        // Specifies the maximum number of unique icons available. By default, IconCaptcha ships with 250 icons.
        'availableIcons' => 250,
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
        // For more information, see https://github.com/fabianwennink/IconCaptcha-PHP/wiki/Challenge-Generator
        'generator' => \IconCaptcha\Challenge\Generators\GD::class,
    ],

    // Configuration for challenge validation.
    'validation' => [
        // Specifies the duration (in seconds) of inactivity before a challenge is invalidated. Set to 0 to disable.
        'inactivityExpiration' => 120,
        // Specifies the duration (in seconds) after a successful challenge before it's invalidated. Set to 0 to disable.
        'completionExpiration' => 300,
        // Specifies the options for challenge solving attempts.
        // For more information, see https://github.com/fabianwennink/IconCaptcha-PHP/wiki/Attempts-&-Timeouts
        'attempts' => [
            // Specifies whether to enable the attempts and timeout feature.
            'enabled' => true,
            // Specifies the maximum number of attempts before the visitor will receive a timeout.
            'amount' => 3,
            // Specifies the time (in seconds) which the visitor has to wait after making too many incorrect attempts.
            'timeout' => 60,
            // Specifies the time (in seconds) after which an attempt will automatically be forgotten and removed from the attempts counter.
            'valid' => 30,
            // Specifies the options for storing attempts and timeout data.
            'storage' => [
                // Specifies the custom driver class to use for storing and retrieving attempts and timeout data.
                // An internal driver compatible with the configured storage driver will be used when set to NULL.
                // For more information, see https://github.com/fabianwennink/IconCaptcha-PHP/wiki/Attempts-&-Timeouts#custom-driver
                // Required:
                // - Your custom driver must extend the '\IconCaptcha\Attempts\Attempts' class.
                // - Your custom driver must be compatible with the configured storage driver.
                'driver' => null,
                // Specifies the options passed on to the driver.
                'options' => [
                    // Specifies the table name used by the database storage drivers to keep track of attempts and timeouts.
                    'table' => 'iconcaptcha_attempts',
                    // Specifies whether the expired attempts/timeout records should automatically be deleted from storage.
                    'purging' => true,
                ],
            ]
        ],
    ],

    // Configuration for the session driver.
    'session' => [
        // Specifies the custom driver class to use for managing challenge data.
        // An internal driver compatible with the configured storage driver will be used when set to NULL.
        // For more information, see https://github.com/fabianwennink/IconCaptcha-PHP/wiki/Session#custom-driver
        // Required:
        // - Your custom driver must extend the '\IconCaptcha\Session\Session' class.
        // - Your custom driver must be compatible with the configured storage driver.
        'driver' => null,
        // Specifies the options passed on to the driver.
        'options' => [
            // Specifies the table name used by the database storage drivers to keep track of challenges.
            'table' => 'iconcaptcha_challenges',
            // Specifies whether the expired challenges should automatically be deleted from storage.
            'purging' => true,
            // Specifies the maximum amount of attempts that will be made to generate a captcha identifier before failing.
            'identifierTries' => 100,
        ],
    ],

    // Configuration for Cross-Origin Resource Sharing (CORS).
    // For more information, see https://github.com/fabianwennink/IconCaptcha-PHP/wiki/Configuration#cors
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

    // Configuration for hooks.
    // For more information, see https://github.com/fabianwennink/IconCaptcha-PHP/wiki/Hooks-&-Events#hooks
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
