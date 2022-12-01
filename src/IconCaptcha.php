<?php

namespace IconCaptcha;

use IconCaptcha\Challenge\Challenge;
use IconCaptcha\Challenge\ValidationResult;
use IconCaptcha\Challenge\Validator;
use IconCaptcha\Token\Token;

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
                $this->options,
                new Challenge($this->options),
                $this->validator
            );
        }
        return $this->request;
    }

    /**
     * @param $request
     * @return ValidationResult
     */
    public function validate($request): ValidationResult
    {
        return $this->validator->validate($request);
    }

    /**
     * Generates the captcha token. The token will be rendered inside a hidden HTML input element.
     * @return string The HTML element containing the token.
     */
    public static function token(): string
    {
        return (new Token())->render();
    }
}
