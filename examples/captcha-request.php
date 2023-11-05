<?php

// Include the captcha classes.
require_once __DIR__ . '/../vendor/autoload.php';

use IconCaptcha\IconCaptcha;

try {

    // Start a session.
    // * Only required when using any 'session' driver in the configuration. See the documentation for more information.
    session_start();

    // Load the IconCaptcha options.
    $options = require 'captcha-config.php';

    // Create an instance of IconCaptcha.
    $captcha = new IconCaptcha($options);

    // Handle the CORS preflight request.
    // * If you have disabled CORS in the configuration, you may remove this line.
    $captcha->handleCors();

    // Process the request.
    $captcha->request()->process();

    // Request was not supported/recognized.
    http_response_code(400);

} catch (Throwable $exception) {

    http_response_code(500);

    // Add your custom error logging handling here.

}
