<?php

/*
 * IconCaptcha - Copyright 2023, Fabian Wennink (https://www.fabianwennink.nl)
 * Licensed under the MIT license: https://www.fabianwennink.nl/projects/IconCaptcha/license
 *
 * The above copyright notice and license shall be included in all copies or substantial portions of the software.
 */

namespace IconCaptcha;

use IconCaptcha\Challenge\Generators\GD;
use IconCaptcha\Token\IconCaptchaToken;

class Options
{
    /**
     * @var mixed Default values for all the server-side options.
     */
    private const DEFAULT_OPTIONS = [
        'iconPath' => null,
        'ipAddress' => null,
        'token' => IconCaptchaToken::class,
        'themes' => [
            'light' => [
                'iconStyle' => 'light',
                'separatorColor' => [240, 240, 240]
            ],
            'dark' => [
                'iconStyle' => 'dark',
                'separatorColor' => [64, 64, 64]
            ],
        ],
        'storage' => [
            'driver' => 'session',
            'connection' => [],
            'datetimeFormat' => 'Y-m-d H:i:s',
        ],
        'challenge' => [
            'availableIcons' => 250,
            'iconAmount' => [
                'min' => 5,
                'max' => 8
            ],
            'rotate' => true,
            'flip' => [
                'horizontally' => true,
                'vertically' => true,
            ],
            'border' => true,
            'generator' => GD::class,
        ],
        'validation' => [
            'inactivityExpiration' => 120,
            'completionExpiration' => 300,
            'attempts' => [
                'enabled' => true,
                'amount' => 5,
                'timeout' => 60,
                'valid' => 60,
                'storage' => [
                    'driver' => null,
                    'options' => [
                        'table' => 'iconcaptcha_attempts',
                        'purging' => true,
                    ],
                ],
            ],
        ],
        'session' => [
            'driver' => null,
            'options' => [
                'table' => 'iconcaptcha_challenges',
                'purging' => true,
                'identifierTries' => 100,
            ],
        ],
        'cors' => [
            'enabled' => false,
            'origins' => [],
            'credentials' => true,
            'cache' => 86400,
        ],
        'hooks' => [
            'init' => null,
            'generation' => null,
            'selection' => null,
        ],
    ];

    /**
     * Set the options for the captcha. The given options will be merged together with the
     * default options and overwrite the default values. If any of the options are missing
     * in the given options array, they will be set with their default value.
     *
     * @param array $options The array of options.
     */
    public static function prepare(array $options): array
    {
        // Merge the given options and default options together.
        $mergedOptions = array_replace_recursive(self::DEFAULT_OPTIONS, $options);

        // If an alternative function to get the visitor's IP address is not defined, use the default 'REMOTE_ADDR' variable.
        if (!isset($options['ipAddress'])) {
            $mergedOptions['ipAddress'] = static fn() => $_SERVER['REMOTE_ADDR'];
        }

        // Trim the custom icon folder path of slashes. If no custom path is set, use the default path.
        // When using Composer, this default path always points to the assets folder in the vendor package.
        if (isset($options['iconPath'])) {
            $mergedOptions['iconPath'] = rtrim($mergedOptions['iconPath'], DIRECTORY_SEPARATOR);
        } else {
            $mergedOptions['iconPath'] = __DIR__ . DIRECTORY_SEPARATOR . '..'
                . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'icons';
        }

        return $mergedOptions;
    }
}
