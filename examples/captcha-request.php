<?php

/**
 * IconCaptcha Plugin: v3.1.0
 * Copyright © 2022, Fabian Wennink (https://www.fabianwennink.nl)
 *
 * Licensed under the MIT license: https://www.fabianwennink.nl/projects/IconCaptcha/license
 */

// Include the captcha classes.
require('../src/IconCaptcha.php');
require('../src/IconCaptchaSession.php');
require('../src/IconCaptchaRequest.php');

use IconCaptcha\IconCaptchaRequest;

// Create an instance of the IconCaptcha request processing class.
$captchaRequest = new IconCaptchaRequest();

// HTTP POST, used when generating and (in)validating the captcha.
if($captchaRequest->isCaptchaAjaxRequest()) {
    $captchaRequest->processAjaxCall();
}

// HTTP GET, used when requesting the captcha image.
if($captchaRequest->isCaptchaImageGenerationRequest()) {
    $captchaRequest->generateCaptchaImage();
}

// Request made to file was not supported.
$captchaRequest->badRequest();
