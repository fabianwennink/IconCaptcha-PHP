<?php

namespace IconCaptcha\Token;

interface IconCaptchaTokenInterface
{
    public static function get();

    public static function render();

    public function validate($payloadToken, $headerToken = null);
}
