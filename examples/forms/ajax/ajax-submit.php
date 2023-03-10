<?php

// Include the IconCaptcha classes.
require_once __DIR__ . '/../../../vendor/autoload.php';

// If the form has been submitted, validate the captcha.
if (!empty($_POST)) {

    // To prevent having to copy the options to every file, a 'config' file was created.
    $options = require __DIR__ . '/../../captcha-config.php';

    // Take a look at the README file to see every available option.
    // All options are optional using default values, apart from the 'iconPath'.
    $captcha = new \IconCaptcha\IconCaptcha($options);

    // Validate the captcha.
    $validation = $captcha->validate($_POST);

    // Confirm the captcha was validated.
    if ($validation->success()) {
        echo '<b>Captcha:</b> The form has been submitted!';
    } else {
        echo '<b>Captcha:</b> Validation failed with error code:' . $validation->getErrorCode();
    }
} else {
    echo '<b>Captcha:</b> No data posted!';
}
