<?php
    /**
     * IconCaptcha Plugin: v3.0.0
     * Copyright © 2021, Fabian Wennink (https://www.fabianwennink.nl)
     *
     * Licensed under the MIT license: http://www.opensource.org/licenses/mit-license.php
     */

    // Start a PHP session.
    session_start();

    // Include the IconCaptcha classes.
    require('../../src/captcha-session.class.php');
    require('../../src/captcha.class.php');

    // Take a look at the README file to see every available option.
    IconCaptcha::options([
        'iconPath' => '../assets/icons/', // required
        'messages' => [
            'wrong_icon' => "You've selected the wrong image.",
            'no_selection' => 'No image has been selected.',
            'empty_form' => "You've not submitted any form.",
            'invalid_id' => 'The captcha ID was invalid.'
        ]
    ]);

    // If the form has been submitted, validate the captcha.
    if(!empty($_POST)) {
        if(IconCaptcha::validateSubmission($_POST)) {
            echo '<b>Captcha:</b> The form has been submitted!';
        } else {
            echo '<b>Captcha: </b>' . IconCaptcha::getErrorMessage();
        }
    }
?>