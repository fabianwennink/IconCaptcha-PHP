<?php

// Include the captcha classes.
require_once __DIR__ . '/../vendor/autoload.php';

use IconCaptcha\IconCaptcha;

try {

    // Start a session.
    // Note: check the documentation to see whether you need this.
    session_start();

    // Initialize the IconCaptcha options.
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

} catch (Throwable $exception) {

    http_response_code(500);

    // ADD YOUR CUSTOM ERROR LOGGING SERVICE HERE.

}
