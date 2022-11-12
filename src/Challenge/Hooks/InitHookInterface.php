<?php

namespace IconCaptcha\Challenge\Hooks;

use IconCaptcha\Session\SessionInterface;

interface InitHookInterface
{
    /**
     * This function will be called when the captcha is being initialized by the visitor. It allows the requested captcha to
     * be immediately completed, showing the visitor a 'verification complete' message instead of a challenge.
     * @param array $request An array containing the contents of the HTTP request.
     * @param SessionInterface $session The session containing captcha information.
     * @param array $options The captcha options.
     * @return bool TRUE if the captcha should autocomplete upon initialization, FALSE if it should not.
     */
    public function shouldImmediatelyComplete($request, $session, $options);
}
