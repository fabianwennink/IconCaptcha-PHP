<?php

/*
 * IconCaptcha - Copyright 2023, Fabian Wennink (https://www.fabianwennink.nl)
 * Licensed under the MIT license: https://www.fabianwennink.nl/projects/IconCaptcha/license
 *
 * The above copyright notice and license shall be included in all copies or substantial portions of the software.
 */

namespace IconCaptcha;

use IconCaptcha\Challenge\Challenge;
use IconCaptcha\Challenge\ValidationResult;
use IconCaptcha\Challenge\Validator;
use IconCaptcha\Storage\StorageFactory;

class IconCaptcha
{
    /**
     * The semantic version number of IconCaptcha, as an integer.
     */
    public const VERSION = 400;

    /**
     * @var mixed Default values for all the server-side options.
     */
    private array $options;

    /**
     * @var Validator The challenge validator.
     */
    private Validator $validator;

    /**
     * @var Request The request handler.
     */
    private Request $request;

    /**
     * @var mixed The storage container.
     */
    private $storage;

    /**
     * Creates a new instance of IconCaptcha.
     *
     * @param array $options The captcha options.
     */
    public function __construct(array $options)
    {
        $this->options = $this->options($options);
        $this->storage = StorageFactory::create($this->options['storage'])->connect();
        $this->validator = new Validator($this->storage, $this->options);
    }

    /**
     * Sets the captcha configuration.
     *
     * @param array $options The captcha configuration.
     * @return array The newly configured options.
     */
    public function options(array $options): array
    {
        return $this->options = Options::prepare($options);
    }

    /**
     * Returns a new request handler instance.
     */
    public function request(): Request
    {
        if (!isset($this->request)) {
            $this->request = new Request(
                new Challenge($this->storage, $this->options),
                $this->validator
            );
        }
        return $this->request;
    }

    /**
     * Validates the form and determines whether the captcha challenge was successfully solved.
     *
     * @param array $request The form POST contents ($_POST)
     * @return ValidationResult The validation result containing whether the challenge was successfully solved.
     */
    public function validate(array $request): ValidationResult
    {
        return $this->validator->validate($request);
    }

    /**
     * Handles the CORS preflight request.
     */
    public function handleCors(): void
    {
        if ($this->options['cors']['enabled']) {
            $cors = new Cors(
                $this->options['cors']['origins'],
                $this->options['cors']['credentials'],
                $this->options['cors']['cache']
            );
            $cors->handleCors();
        }
    }
}
