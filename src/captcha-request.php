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

use IconCaptcha\IconCaptcha;

// TODO The options should be available here as well for the captcha.class.php

header('Access-Control-Allow-Methods: OPTIONS, GET, POST');
// header("Access-Control-Allow-Origin: *");

// HTTP GET
if (isset($_GET['payload']) && !isAjaxRequest()) {

    // Decode the payload.
    $payload = decodePayload($_GET['payload']);

    // Validate the payload content.
    if(!isset($payload, $payload['i']) || !is_numeric($payload['i'])) {
        badRequest();
    }

    IconCaptcha::getImage($payload['i']);
    exit;
}

// HTTP POST
if (!empty($_POST) && isAjaxRequest() && isset($_POST['payload'])) {

    // Decode the payload.
    $payload = decodePayload($_POST['payload']);

    // Validate the payload content.
    if (!isset($payload, $payload['a'], $payload['i']) || !is_numeric($payload['a']) || !is_numeric($payload['i'])) {
        badRequest();
    }

    switch ((int)$payload['a']) {
        case 1: // Requesting the image hashes

            // Validate the theme name. Fallback to light.
            $theme = (isset($payload['t']) && is_string($payload['t'])) ? $payload['t'] : 'light';

            // Echo the captcha data.
            header('HTTP/1.0 200 OK');
            header('Content-type: text/plain');
            exit(IconCaptcha::getCaptchaData($theme, $payload['i']));
        case 2: // Setting the user's choice
            if (IconCaptcha::setSelectedAnswer($payload)) {
                header('HTTP/1.0 200 OK');
                exit;
            }
            break;
        case 3: // Captcha interaction time expired.
            IconCaptcha::invalidateCaptcha($payload['i']);
            header('HTTP/1.0 200 OK');
            exit;
        default:
            break;
    }
}

badRequest();

// Adds another level of security to the Ajax call.
// Only requests made through Ajax are allowed.
function isAjaxRequest() {
    return (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');
}

// Tries to decode the given base64 and json encoded payload.
function decodePayload($payload) {

    // Base64 decode the payload.
    $payload = base64_decode($payload);
    if($payload === false) {
        badRequest();
    }

    // JSON decode the payload.
    return json_decode($payload, true);
}

// Exits the request with a 400 bad request status.
function badRequest() {
    header('HTTP/1.1 400 Bad Request');
    exit;
}