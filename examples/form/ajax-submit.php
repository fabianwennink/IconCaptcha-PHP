<?php

/**
 * IconCaptcha Plugin: v3.1.0
 * Copyright © 2022, Fabian Wennink (https://www.fabianwennink.nl)
 *
 * Licensed under the MIT license: https://www.fabianwennink.nl/projects/IconCaptcha/license
 */

// Start a PHP session.
session_start();

// Include the IconCaptcha classes.
require('../../src/IconCaptchaSessionInterface.php');
require('../../src/IconCaptchaSession.php');
require('../../src/IconCaptchaOptions.php');
require('../../src/IconCaptcha.php');

use IconCaptcha\IconCaptcha;

// If the form has been submitted, validate the captcha.
if(!empty($_POST)) {

    // To prevent having to copy the options to every file, a 'config' file was created.
    $options = require('../captcha-config.php');

    // Take a look at the README file to see every available option.
    // All options are optional using default values, apart from the 'iconPath'.
    $captcha = new IconCaptcha($options);

    if($captcha->validateSubmission($_POST)) {
        echo '<b>Captcha:</b> The form has been submitted!';
    } else {
        echo '<b>Captcha: </b>' . $captcha->getErrorMessage();
    }
} else {
    echo '<b>Captcha:</b> No data posted!';
}
