<?php

namespace IconCaptcha;

use IconCaptcha\Challenge\Challenge;
use IconCaptcha\Challenge\Validator;

class IconCaptcha
{
    /**
     * @var mixed Default values for all the server-side options.
     */
    private array $options;

    private Validator $validator;

    private Request $request;

    public function __construct($options)
    {
        $this->options($options);
        $this->validator = new Validator($options);
    }

    public function options($options): void
    {
        $this->options = Options::prepare($options);
    }

    /**
     * @return Request
     */
    public function request(): Request
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
    public function validate($request): object
    {
        return $this->validator->validate($request);
    }
}
