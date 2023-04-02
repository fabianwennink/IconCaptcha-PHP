<?php

/**
 * IconCaptcha Plugin: v3.1.2
 * Copyright © 2023, Fabian Wennink (https://www.fabianwennink.nl)
 *
 * Licensed under the MIT license: https://www.fabianwennink.nl/projects/IconCaptcha/license
 */

// Start a PHP session.
session_start();

// Include the IconCaptcha classes.
require('../../src/captcha-session.class.php');
require('../../src/captcha.class.php');

use IconCaptcha\IconCaptcha;

// If the form has been submitted, validate the captcha.
if(!empty($_POST)) {
    if(IconCaptcha::validateSubmission($_POST)) {
        echo '<b>Captcha:</b> The form has been submitted!';
    } else {
        echo '<b>Captcha: </b>' . IconCaptcha::getErrorMessage();
    }
} else {
    echo '<b>Captcha:</b> No data posted!';
}
