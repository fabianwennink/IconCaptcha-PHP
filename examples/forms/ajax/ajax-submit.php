<?php

// Include the IconCaptcha classes.
require_once __DIR__ . '/../../../vendor/autoload.php';

// If the form has been submitted, validate the captcha.
if (!empty($_POST)) {

    // Start a session.
    // * Required when using any 'session' driver in the configuration.
    // * Required when using the IconCaptcha Token in your forms.
    // For more information, please refer to the documentation.
    session_start();

    // Load the IconCaptcha options.
    $options = require __DIR__ . '/../../captcha-config.php';

    // Create an instance of IconCaptcha.
    $captcha = new \IconCaptcha\IconCaptcha($options);

    // Validate the captcha.
    $validation = $captcha->validate($_POST);

    // Confirm the captcha was validated.
    if ($validation->success()) {
        echo '<b>Captcha:</b> The form has been submitted!';
    } else {
        echo '<b>Captcha:</b> Validation failed with error code: ' . $validation->getErrorCode();
    }
} else {
    echo '<b>Captcha:</b> No data posted!';
}
