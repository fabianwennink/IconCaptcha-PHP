<?php
    /**
     * Icon Captcha Plugin: v2.0.2
     * Copyright © 2017, Fabian Wennink (https://www.fabianwennink.nl)
     *
     * Licensed under the MIT license: http://www.opensource.org/licenses/mit-license.php
     */

    // Start a PHP session
    session_start();

    // Include the captcha class
    require('captcha.class.php');

    // HTTP POST - Either the captcha has been submitted or the
    if(!empty($_POST) && isAjaxRequest()) {
        if((isset($_POST['rT']) && is_numeric($_POST['rT'])) && (isset($_POST['cID']) && is_numeric($_POST['cID']))) {
            switch((int)$_POST['rT']) {
                case 1: // Requesting the image hashes
                    $captcha_theme = (isset($_POST['tM']) && ($_POST['tM'] === "light" || $_POST['tM'] === "dark")) ? $_POST['tM'] : "light";

                    echo IconCaptcha::getCaptchaData($captcha_theme, $_POST['cID']);
                    exit;
                case 2: // Setting the user's choice
                    echo IconCaptcha::setSelectedAnswer($_POST);
                    exit;
                default:
                    break;
            }
        }
    }

    // HTTP GET - Requesting the actual images
    if((!empty($_GET) && isset($_GET['hash']) && strlen($_GET['hash']) === 48) && (isset($_GET['cid']) && is_numeric($_GET['cid']))) {
        IconCaptcha::getIconFromHash($_GET['hash'], $_GET['cid']);
        exit;
    }

    echo 'Invalid request.';

    // Adds another level of security to the Ajax call.
    // Only requests made through Ajax are allowed.
    // NOTE: THE HEADER CAN BE SPOOFED
    function isAjaxRequest() {
        return (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');
    }
?>