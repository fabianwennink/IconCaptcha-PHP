<?php

/**
 * IconCaptcha Plugin: v3.1.2
 * Copyright © 2023, Fabian Wennink (https://www.fabianwennink.nl)
 *
 * Licensed under the MIT license: https://www.fabianwennink.nl/projects/IconCaptcha/license
 */

// Start a PHP session.
session_start();

// Include the captcha classes.
require('captcha-session.class.php');
require('captcha.class.php');

use IconCaptcha\IconCaptcha;

// HTTP GET
if (isset($_GET['payload']) && !isAjaxRequest()) {

    // Decode the payload.
    $payload = decodePayload($_GET['payload']);

    // Validate the payload content.
    if (!isset($payload, $payload['i']) || !is_numeric($payload['i'])) {
        badRequest();
    }

    // Validate the captcha token.
    if (!validToken($payload, false)) {
        tokenError();
    }

    IconCaptcha::getImage($payload['i']);
    exit;
}

const HTTP_STATUS_OK = 'HTTP/1.0 200 OK';
const CUSTOM_TOKEN_HEADER = 'HTTP_X_ICONCAPTCHA_TOKEN';

// HTTP POST
if (!empty($_POST) && isAjaxRequest() && isset($_POST['payload'])) {

    // Decode the payload.
    $payload = decodePayload($_POST['payload']);

    // Validate the payload content.
    if (!isset($payload, $payload['a'], $payload['i']) || !is_numeric($payload['a']) || !is_numeric($payload['i'])) {
        badRequest();
    }

    // Validate the captcha token.
    if (!validToken($payload, true)) {
        tokenError();
    }

    switch ((int)$payload['a']) {
        case 1: // Requesting the image hashes

            // Validate the theme name. Fallback to light.
            $theme = (isset($payload['t']) && is_string($payload['t'])) ? $payload['t'] : 'light';

            // Echo the captcha data.
            header(HTTP_STATUS_OK);
            header('Content-type: text/plain');
            exit(IconCaptcha::getCaptchaData($theme, $payload['i']));
        case 2: // Setting the user's choice
            if (IconCaptcha::setSelectedAnswer($payload)) {
                header(HTTP_STATUS_OK);
                exit;
            }
            break;
        case 3: // Captcha interaction time expired.
            IconCaptcha::invalidateSession($payload['i']);
            header(HTTP_STATUS_OK);
            exit;
        default:
            break;
    }
}

// No more actions available, bad request.
badRequest();

// Adds another level of security to the Ajax call.
// Only requests made through Ajax are allowed.
function isAjaxRequest()
{
    return (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');
}

// Validates the payload and possibly the header tokens.
function validToken($payload, $checkHeader)
{
    $payloadToken = null;
    $headerToken = null;

    // Get the token from the payload.
    if (!empty($payload['tk'])) {
        $payloadToken = $payload['tk'];
    }

    // Get the token from the request headers, can be empty for some requests.
    if ($checkHeader && !empty($_SERVER[CUSTOM_TOKEN_HEADER])) {
        $headerToken = $_SERVER[CUSTOM_TOKEN_HEADER];
    }

    // Validate the tokens.
    return IconCaptcha::validateToken($payloadToken, $headerToken);
}

// Create a error message string for the token validation error.
function tokenError()
{
    header(HTTP_STATUS_OK);
    header('Content-type: text/plain');
    exit(base64_encode(json_encode(['error' => 2])));
}

// Tries to decode the given base64 and json encoded payload.
function decodePayload($payload)
{
    // Base64 decode the payload.
    $payload = base64_decode($payload);
    if ($payload === false) {
        badRequest();
    }

    // JSON decode the payload.
    return json_decode($payload, true);
}

// Exits the request with a 400 bad request status.
function badRequest()
{
    header('HTTP/1.1 400 Bad Request');
    exit;
}
