<?php

namespace IconCaptcha;

use IconCaptcha\Challenge\Generators\GD;
use IconCaptcha\Session\Drivers\ServerSession;
use IconCaptcha\Token\Token;

class Options
{
    /**
     * @var mixed Default values for all the server-side options.
     */
    private const DEFAULT_OPTIONS = [
        'iconPath' => null,
        'themes' => [
            'light' => ['icons' => 'light', 'color' => [240, 240, 240]],
            'dark' => ['icons' => 'dark', 'color' => [64, 64, 64]],
        ],
        'challenge' => [
            'inactivityExpiration' => 120, // In seconds. Set to 0 to disable.
            'completionExpiration' => 300, // In seconds. Set to 0 to disable.
            'latencyCorrection' => true,
        ],
        'image' => [
            'icons' => 180,
            'amount' => [ // min & max can be 5 - 8
                'min' => 5,
                'max' => 8
            ],
            'rotate' => true,
            'flip' => [
                'horizontally' => true,
                'vertically' => true,
            ],
            'border' => true
        ],
        'attempts' => [
            'amount' => 5,
            'timeout' => 30 // seconds.
        ],
        'cors' => [
            'enabled' => false,
            'origins' => [],
            'credentials' => true,
            'cache' => 86400,
        ],
        'token' => Token::class,
        'generator' => GD::class,
        'hooks' => [
            'init' => null, // initialization hook, called when the challenge is requested.
            'generation' => null, // image generation hook, e.g. for changing something on image.
            'selection' => null, // user image interaction hook, called when the user clicks on an icon.
        ],
        'session' => [
            'driver' => ServerSession::class,
            'options' => [],
        ]
    ];

    /**
     * Set the options for the captcha. The given options will be merged together with the
     * default options and overwrite the default values. If any of the options are missing
     * in the given options array, they will be set with their default value.
     * @param array $options The array of options.
     */
    public static function prepare(array $options): array
    {
        // Merge the given options and default options together.
        $mergedOptions = array_replace_recursive(self::DEFAULT_OPTIONS, $options);

        // Trim the custom icon folder path of slashes. If no custom path is set, use the default path.
        // When using Composer, the 'iconPath' option always points to the default path in the vendor folder.
        if (isset($options['iconPath'])) {
            $mergedOptions['iconPath'] = rtrim($mergedOptions['iconPath'], DIRECTORY_SEPARATOR);
        } else {
            $mergedOptions['iconPath'] = __DIR__ . DIRECTORY_SEPARATOR . '..'
                . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'icons';
        }

        return $mergedOptions;
    }
}
