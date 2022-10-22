<?php

/**
 * IconCaptcha Plugin: v3.1.0
 * Copyright © 2022, Fabian Wennink (https://www.fabianwennink.nl)
 *
 * Licensed under the MIT license: https://www.fabianwennink.nl/projects/IconCaptcha/license
 */

namespace IconCaptcha;

use IconCaptcha\Session\IconCaptchaSession;
use IconCaptcha\Token\IconCaptchaToken;

class IconCaptchaOptions
{
    /**
     * @var mixed Default values for all the server-side options.
     */
    const DEFAULT_OPTIONS = [
        'iconPath' => null,
        'themes' => [
            'light' => ['icons' => 'light', 'color' => [240, 240, 240]],
            'legacy-light' => ['icons' => 'light', 'color' => [240, 240, 240]],
            'dark' => ['icons' => 'dark', 'color' => [64, 64, 64]],
            'legacy-dark' => ['icons' => 'dark', 'color' => [64, 64, 64]],
        ],
        'messages' => [
            'wrong_icon' => 'You\'ve selected the wrong image.',
            'no_selection' => 'No image has been selected.',
            'empty_form' => 'You\'ve not submitted any form.',
            'invalid_id' => 'The captcha ID was invalid.',
            'form_token' => 'The form token was invalid.'
        ],
        'image' => [
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
        'token' => IconCaptchaToken::class,
        'session' => IconCaptchaSession::class,
    ];

    /**
     * Set the options for the captcha. The given options will be merged together with the
     * default options and overwrite the default values. If any of the options are missing
     * in the given options array, they will be set with their default value.
     * @param array $options The array of options.
     */
    public static function prepare($options = [])
    {
        // Merge the given options and default options together.
        $mergedOptions = array_merge(self::DEFAULT_OPTIONS, $options);

        // Merge the extra themes with the default themes.
        if(isset($options['themes']) && is_array($options['themes'])) {
            $mergedOptions['themes'] = array_merge(self::DEFAULT_OPTIONS['themes'], $options['themes']);
        }

        // Trim the custom icon folder path of slashes. If no custom path is set, use the default path.
        // When using Composer, the 'iconPath' option always points to the default path in the vendor folder.
        if(isset($options['iconPath'])) {
            $mergedOptions['iconPath'] = rtrim($mergedOptions['iconPath'], DIRECTORY_SEPARATOR);
        } else {
            $mergedOptions['iconPath'] = dirname(__FILE__) . DIRECTORY_SEPARATOR . '..'
                . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'icons';
        }

        return $mergedOptions;
    }
}
