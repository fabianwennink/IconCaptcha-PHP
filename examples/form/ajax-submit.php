<?php
    /**
     * IconCaptcha Plugin: v2.1.3
     * Copyright © 2017, Fabian Wennink (https://www.fabianwennink.nl)
     *
     * Licensed under the MIT license: http://www.opensource.org/licenses/mit-license.php
     */

    // Start a PHP session.
    session_start();

    // Include the IconCaptcha class.
    require('../../resources/php/captcha.class.php');

    // Set the path to the captcha icons. Set it as if you were
    // currently in the PHP folder containing the captcha.class.php file.
    // ALWAYS END WITH A /
    // DEFAULT IS SET TO ../icons/
    IconCaptcha::setIconsFolderPath("../icons/");

    // Use custom messages as error messages (optional).
    // Take a look at the IconCaptcha class to see what each string means.
    // IconCaptcha::setErrorMessages(array('', '', '', ''));

    // If the form has been submitted, validate the captcha.
    if(!empty($_POST)) {
        if(IconCaptcha::validateSubmission($_POST)) {
            echo '<b>Captcha:</b> The form has been submitted!';
        } else {
            echo '<b>Captcha: </b>' . json_decode(IconCaptcha::getErrorMessage())->error;
        }
    }
?>