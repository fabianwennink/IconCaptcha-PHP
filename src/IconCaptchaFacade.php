<?php

namespace IconCaptcha;

class IconCaptchaFacade
{
    /**
     * @var IconCaptcha
     */
    private $iconCaptcha;

    /**
     * @var IconCaptchaRequest
     */
    private $iconCaptchaRequest;

    public function __construct($options)
    {
        $this->iconCaptcha = new IconCaptcha($options);
    }

    public function options($options)
    {
        $this->iconCaptcha->options($options);
    }

    public function request()
    {
        if(!isset($this->iconCaptchaRequest)) {
            $this->iconCaptchaRequest = new IconCaptchaRequest($this->iconCaptcha);
        }
        return $this->iconCaptchaRequest;
    }

    public function validate($request)
    {
        return $this->iconCaptcha->validate($request);
    }

    public function error()
    {
        return $this->iconCaptcha->getErrorMessage();
    }
}
