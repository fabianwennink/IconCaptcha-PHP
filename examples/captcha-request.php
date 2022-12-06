<?php

/**
 * IconCaptcha Plugin: v3.1.0
 * Copyright © 2022, Fabian Wennink (https://www.fabianwennink.nl)
 *
 * Licensed under the MIT license: https://www.fabianwennink.nl/projects/IconCaptcha/license
 */

// Include the captcha classes.
require_once __DIR__ . '/../vendor/autoload.php';

use IconCaptcha\IconCaptcha;

// Start a session.
session_start();

// To prevent having to copy the options to every file, a 'config' file was created.
$options = require 'captcha-config.php';

// Create an instance of IconCaptcha.
$captcha = new IconCaptcha($options);

// Handle the CORS preflight request.
$captcha->handleCors();

// Process the request.
$captcha->request()->process();

// Request was not supported/recognized.
http_response_code(400);
