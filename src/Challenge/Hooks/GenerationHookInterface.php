<?php

namespace IconCaptcha\Challenge\Hooks;

use IconCaptcha\Session\SessionInterface;

interface GenerationHookInterface
{
    /**
     * Will be called when the captcha challenge image was generated. Allows for manipulation of the image.
     * @param mixed $image The generated challenge image.
     * @param SessionInterface $session The session containing captcha information.
     * @param array $options The captcha options.
     * @return mixed The manipulated challenge image.
     */
    public function generate($image, $session, $options);
}
