<?php

namespace IconCaptcha;

use IconCaptcha\Challenge\Challenge;
use IconCaptcha\Challenge\Validator;

class IconCaptcha
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
     * @var Request
     */
    private $request;

    public function __construct($options)
    {
        $this->options($options);
        $this->validator = new Validator($options);
    }

    public function options($options)
    {
        $this->options = Options::prepare($options);
    }

    /**
     * @return Request
     */
    public function request()
    {
        if(!isset($this->request)) {
            $this->request = new Request(
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
