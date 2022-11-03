<?php

/**
 * IconCaptcha Plugin: v3.1.0
 * Copyright © 2022, Fabian Wennink (https://www.fabianwennink.nl)
 *
 * Licensed under the MIT license: https://www.fabianwennink.nl/projects/IconCaptcha/license
 */

// Include the captcha classes.
require_once '../vendor/autoload.php';

use IconCaptcha\IconCaptcha;

// To prevent having to copy the options to every file, a 'config' file was created.
$options = require_once 'captcha-config.php';

// Create an instance of the IconCaptcha request processing class.
$captchaRequest = (new IconCaptcha($options))->request();

// HTTP POST, used when generating and (in)validating the captcha.
if($captchaRequest->isCaptchaAjaxRequest()) {
    $captchaRequest->processAjaxCall();
}

// HTTP GET, used when requesting the captcha image.
if($captchaRequest->isChallengeRenderRequest()) {
    $captchaRequest->renderChallenge();
}

// Request made to file was not supported.
$captchaRequest->badRequest();
