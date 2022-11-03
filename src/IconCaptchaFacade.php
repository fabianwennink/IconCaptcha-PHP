<?php

namespace IconCaptcha;

use IconCaptcha\Challenge\Challenge;
use IconCaptcha\Challenge\Validator;

class IconCaptchaFacade
{
    /**
     * @var mixed Default values for all the server-side options.
     */
    private $options;

    /**
     * @var Validator
     */
    private $validator;

    /**
     * @var IconCaptchaRequest
     */
    private $request;

    public function __construct($options)
    {
        $this->options($options);
        $this->validator = new Validator($options);
    }

    public function options($options)
    {
        $this->options = IconCaptchaOptions::prepare($options);
    }

    /**
     * @return IconCaptchaRequest
     */
    public function request()
    {
        if(!isset($this->request)) {
            $this->request = new IconCaptchaRequest(
                new Challenge($this->options),
                $this->validator
            );
        }
        return $this->request;
    }

    /**
     * @param $request
     * @return object
     */
    public function validate($request)
    {
        return $this->validator->validate($request);
    }
}
