<?php

namespace IconCaptcha\Challenge\Hooks;

use IconCaptcha\Session\SessionInterface;

interface GenerationHookInterface
{
    /**
     * Will be called when the challenge image was generated. This hook allows for additional changed to be made to the image.
     *
     * @param array $request An array containing the contents of the HTTP request.
     * @param SessionInterface $session The session containing captcha information.
     * @param array $options The captcha options.
     * @param mixed $image The generated challenge image.
     *
     * @return mixed The updated challenge image.
     */
    public function generate(array $request, SessionInterface $session, array $options, $image);
}
