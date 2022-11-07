<?php

/**
 * IconCaptcha Plugin: v3.1.1
 * Copyright © 2022, Fabian Wennink (https://www.fabianwennink.nl)
 *
 * Licensed under the MIT license: https://www.fabianwennink.nl/projects/IconCaptcha/license
 */

// Include the IconCaptcha classes.
require_once '../../../vendor/autoload.php';

// If the form has been submitted, validate the captcha.
if(!empty($_POST)) {

    // To prevent having to copy the options to every file, a 'config' file was created.
    $options = require_once '../../captcha-config.php';

    // Take a look at the README file to see every available option.
    // All options are optional using default values, apart from the 'iconPath'.
    $captcha = new \IconCaptcha\IconCaptcha($options);

    // Validate the captcha.
    $response = $captcha->validate($_POST);

    // Confirm the captcha was validated.
    if($response->success === true) {
        echo '<b>Captcha:</b> The form has been submitted!';
    } else {
        echo '<b>Captcha: </b>' . $response->error['message'];
    }
} else {
    echo '<b>Captcha:</b> No data posted!';
}
