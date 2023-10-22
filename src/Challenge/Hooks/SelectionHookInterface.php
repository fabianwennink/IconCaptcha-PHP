<?php

/*
 * IconCaptcha - Copyright 2023, Fabian Wennink (https://www.fabianwennink.nl)
 * Licensed under the MIT license: https://www.fabianwennink.nl/projects/IconCaptcha/license
 *
 * The above copyright notice and license shall be included in all copies or substantial portions of the software.
 */

namespace IconCaptcha\Challenge\Hooks;

use IconCaptcha\Session\SessionInterface;

interface SelectionHookInterface
{
    /**
     * Will be called when the visitor selected the correct icon, resulting in a successful completion of the captcha.
     *
     * @param array $request An array containing the contents of the HTTP request.
     * @param SessionInterface $session The session containing captcha information.
     * @param array $options The captcha options.
     */
    public function correct(array $request, SessionInterface $session, array $options): void;

    /**
     * Will be called when the visitor selected an incorrect icon.
     *
     * @param array $request An array containing the contents of the HTTP request.
     * @param SessionInterface $session The session containing captcha information.
     * @param array $options The captcha options.
     */
    public function incorrect(array $request, SessionInterface $session, array $options): void;
}
