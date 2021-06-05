<?php

/**
 * IconCaptcha Plugin: v3.0.0
 * Copyright © 2021, Fabian Wennink (https://www.fabianwennink.nl)
 *
 * Licensed under the MIT license: http://www.opensource.org/licenses/mit-license.php
 */

// Start a PHP session.
session_start();

// Include the captcha classes.
require('captcha-session.class.php');
require('captcha.class.php');

// HTTP GET - Requesting the actual image.
if ((isset($_GET['hash']) && strlen($_GET['hash']) === 48) &&
    (isset($_GET['cid']) && is_numeric($_GET['cid'])) && !isAjaxRequest()) {
    IconCaptcha::getIconFromHash($_GET['hash'], $_GET['cid']);
    exit;
}

// HTTP POST - Either the captcha has been submitted or an image has been selected by the user.
if (!empty($_POST) && isAjaxRequest()) {
    if (isset($_POST['rT']) && is_numeric($_POST['rT']) && isset($_POST['cID']) && is_numeric($_POST['cID'])) {
        switch ((int)$_POST['rT']) {
            case 1: // Requesting the image hashes
                $captcha_theme = (isset($_POST['tM']) && ($_POST['tM'] === 'light' || $_POST['tM'] === 'dark')) ? $_POST['tM'] : 'light';

                // Echo the JSON encoded array
                header('Content-type: application/json');
                exit(IconCaptcha::getCaptchaData($captcha_theme, $_POST['cID']));
            case 2: // Setting the user's choice
                if (IconCaptcha::setSelectedAnswer($_POST)) {
                    header('HTTP/1.0 200 OK');
                    exit;
                }
                break;
            default:
                break;
        }
    }
}

header('HTTP/1.1 400 Bad Request');
exit;

// Adds another level of security to the Ajax call.
// Only requests made through Ajax are allowed.
function isAjaxRequest()
{
    return (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');
}