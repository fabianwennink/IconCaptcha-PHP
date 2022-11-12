<?php

namespace IconCaptcha\Challenge\Hooks;

use IconCaptcha\Session\SessionInterface;

class Hook
{
    /**
     * Attempts to call the given hook and return the value generated by the hook.
     * In case the given hook does not exist, the given default value will be returned.
     * @param string $type The name of the hook in the options.
     * @param mixed $class The interface which the hook has to implement in order to be called properly.
     * @param string $action The name of the function defined in the interface, which should be called.
     * @param SessionInterface $session The session containing captcha information.
     * @param array $options The captcha options.
     * @param mixed $default The default value, which will be returned if the hook could not be called.
     * @param mixed ...$params Any additional data which has to be passed to the hook.
     * @return mixed The result of the hook, or the default value if no hook was called.
     */
    public static function call(string $type, $class, string $action, SessionInterface $session, array $options, $default, ...$params)
    {
        $hook = self::getHook($type, $class);

        if(!empty($hook)) {
            return $hook->{$action}($_REQUEST, $session, $options, $params);
        }

        return $default;
    }

    /**
     * Attempts to call the given hook.
     * @param string $type The name of the hook in the options.
     * @param mixed $class The interface which the hook has to implement in order to be called properly.
     * @param string $action The name of the function defined in the interface, which should be called.
     * @param SessionInterface $session The session containing captcha information.
     * @param array $options The captcha options.
     * @param mixed ...$params Any additional data which has to be passed to the hook.
     */
    public static function callVoid(string $type, $class, string $action, SessionInterface $session, array $options, ...$params)
    {
        $hook = self::getHook($type, $class);

        if(!empty($hook)) {
            $hook->{$action}($_REQUEST, $session, $options, $params);
        }
    }

    /**
     * Attempts to return a class instance of the hook based on the given hook name.
     * @param string $hookName The name of the hook in the options.
     * @param mixed $interface The interface which the hook has to implement in order to be called properly.
     * @return mixed|null The hook class instance, or NULL if no hook was defined for the current action.
     */
    private static function getHook(string $hookName, $interface)
    {
        if(isset($options['hooks'][$hookName])) {
            $hook = new $options['hooks'][$hookName]();
            if($hook instanceof $interface) {
                return $hook;
            }
        }
        return null;
    }
}
