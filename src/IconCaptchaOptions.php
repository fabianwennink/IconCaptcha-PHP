<?php

/**
 * IconCaptcha Plugin: v3.1.0
 * Copyright © 2022, Fabian Wennink (https://www.fabianwennink.nl)
 *
 * Licensed under the MIT license: https://www.fabianwennink.nl/projects/IconCaptcha/license
 */

namespace IconCaptcha;

class IconCaptchaOptions
{
    const DEFAULT_BORDER_COLOR = [240, 240, 240];

    /**
     * @var mixed Default values for all the server-side options.
     */
    const DEFAULT_OPTIONS = [
        'iconPath' => null, // required
        'themes' => [
            'light' => ['icons' => 'light', 'color' => self::DEFAULT_BORDER_COLOR],
            'legacy-light' => ['icons' => 'light', 'color' => self::DEFAULT_BORDER_COLOR],
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
        $mergedOptions['themes'] = array_merge(self::DEFAULT_OPTIONS['themes'], $options['themes']);

        // TODO improve the option merging.

        // Update the icon path string.
        $mergedOptions['iconPath'] = (is_string($mergedOptions['iconPath'])) ? rtrim($mergedOptions['iconPath'], '/') : '';

        return $mergedOptions;
    }
}
