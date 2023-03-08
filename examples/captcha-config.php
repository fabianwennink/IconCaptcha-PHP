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
    'challenge' => [
        'inactivityExpiration' => 120, // In seconds. Set to 0 to disable.
        'completionExpiration' => 300, // In seconds. Set to 0 to disable.
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
        'amount' => 3,
        'timeout' => 60 // seconds.
    ],
    'token' => \IconCaptcha\Token\Token::class, // to disable, replace with 'null'.
    'session' => \IconCaptcha\Session\Drivers\ServerSession::class,
    'generator' => \IconCaptcha\Challenge\Generators\GD::class, // a generator for ImageMagick (Imagick::class) is also available.
    'session' => [
        'driver' => \IconCaptcha\Session\Drivers\ServerSession::class, // database drivers are available as well. (\Session\Drivers\Database\)
//        'options' => [
//            // Connection details are required when using a database session driver.
//            'connection' => [
//                //'url' => 'mysql:host=127.0.0.1;port=3306;dbname=db', // you can use a DSN URL if your database requires a more complex connection.
//                'host' => '127.0.0.1',
//                'port' => 3306,
//                'database' => 'db',
//                'username' => 'root',
//                'password' => '',
//                'table' => 'sessions',
//            ],
////
////            // ... or use an existing database connection (must be a PDO object).
////            'connection' => $connection
//        ],
    ]
];
