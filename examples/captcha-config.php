<?php

// All options are optional, and are using their default values.
// Take a look at the GitHub Wiki for information about every option.
return [
    //'iconPath' => dirname(__FILE__) . '/../assets/icons/',
    //'themes' => [
    //    'black' => [
    //        'icons' => 'light', // Which icon type should be used: light or dark.
    //        'color' => [20, 20, 20], // Array contains the icon separator border color, as RGB.
    //    ]
    //],
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
        'amount' => 3,
        'timeout' => 60 // seconds.
    ],
    'token' => \IconCaptcha\Token\Token::class, // to disable, replace with 'null'.
    'session' => \IconCaptcha\Session\Session::class,
    'generator' => \IconCaptcha\Challenge\Generators\GD::class, // a generator for ImageMagick (Imagick::class) is also available.
];
