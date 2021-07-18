<?php

/**
 * IconCaptcha Plugin: v3.0.0
 * Copyright © 2021, Fabian Wennink (https://www.fabianwennink.nl)
 *
 * Licensed under the MIT license: http://www.opensource.org/licenses/mit-license.php
 */

class IconCaptcha
{
    const ICON_CAPTCHA = 'icon_captcha';
    const CAPTCHA_ICON_PATH = 'icon_path';
    const CAPTCHA_FIELD_SELECTION = 'ic-hf-se';
    const CAPTCHA_FIELD_ID = 'ic-hf-id';
    const CAPTCHA_FIELD_HONEYPOT = 'ic-hf-hp';
    const CAPTCHA_SIZE = 320;
    const CAPTCHA_ICONS_AMOUNT = 91;
    const CAPTCHA_ICON_SIZES = [6 => 40, 7 => 30];

    /**
     * @var string A JSON encoded error message, which will be shown to the user.
     */
    private static $error;

    /**
     * @var CaptchaSession The session containing captcha information.
     */
    private static $session;

    /**
     * @var mixed Default values for all the server-side options.
     */
    private static $options = [
        'iconPath' => null, // required
        'messages' => [
            'wrong_icon' => 'You\'ve selected the wrong image.',
            'no_selection' => 'No image has been selected.',
            'empty_form' => 'You\'ve not submitted any form.',
            'invalid_id' => 'The captcha ID was invalid.'
        ]
    ];

    /**
     * Set the options for the captcha.
     * @param array $options The array of options.
     */
    public static function options($options)
    {
        self::$options = array_merge(self::$options, $options);

        // Update the icon path string.
        self::$options['iconPath'] = (is_string(self::$options['iconPath'])) ? rtrim(self::$options['iconPath'], '/') : '';

        // TODO save options to session, fetch when needed.

        // TODO remove when options are saved in session.
        $_SESSION[self::ICON_CAPTCHA][self::CAPTCHA_ICON_PATH] = self::$options['iconPath'];
    }

    /**
     * Returns the validation error message.
     *
     * @return string The JSON encoded error message containing the error ID and message.
     */
    public static function getErrorMessage()
    {
        return !empty(self::$error) ? json_decode(self::$error)->error : '';
    }

    /**
     * Return a random captcha identifier as a base64 encoded string.
     *
     * @param string $theme The theme of the captcha.
     * @param int $captchaIdentifier The identifier of the captcha.
     *
     * @return string Captcha details required to initialize the UI with.
     */
    public static function getCaptchaData($theme, $captchaIdentifier)
    {
        // Set the captcha id property
        self::tryCreateSession($captchaIdentifier);

        $a = mt_rand(1, self::CAPTCHA_ICONS_AMOUNT); // Get a random number (correct image)
        $b = 0; // Incorrect image placeholder.

        $e = mt_rand(6, 7); // Number of icons in image. TODO
        $f = $e === 7 ? mt_rand(1, 3) : mt_rand(1, 2); // Number of times the incorrect image will be placed onto the placeholder.

        $d = []; // At which position the correct image will be placed.
        for ($i = 0; $i < $f; $i++) {
            $d[] = mt_rand(1, $e);
        }

        // Pick a random number for the incorrect icon.
        // Loop until a number is found which doesn't match the correct icon ID.
        while ($b === 0) {
            $c = mt_rand(1, self::CAPTCHA_ICONS_AMOUNT);
            if ($c !== $a) {
                $b = $c;
            }
        }

        // Unset the previous session data
        self::$session->clear();

        // Set the chosen icons and position and reset the requested status.
        self::$session->mode = $theme;
        self::$session->icons = [$a, $b, $e, $f];
        self::$session->positions = $d;
        self::$session->requested = false;
        self::$session->save();

        // Return the captcha details.
        return base64_encode(json_encode([
            'icons' => $e
        ]));
    }

    /**
     * Validates the user form submission. If the captcha is incorrect, it
     * will set the error variable and return false, else true.
     *
     * @param array $post The HTTP POST request.
     *
     * @return boolean TRUE if the captcha was correct, FALSE if not.
     */
    public static function validateSubmission($post)
    {
        // Make sure the form data is set.
        if (empty($post)) {
            self::$error = json_encode(['id' => 3, 'error' => self::$options['messages']['empty_form']]);
            return false;
        }

        // Check if the captcha ID is set.
        if (!isset($post[self::CAPTCHA_FIELD_ID]) || !is_numeric($post[self::CAPTCHA_FIELD_ID])
            || !CaptchaSession::exists($post[self::CAPTCHA_FIELD_ID])) {
            self::$error = json_encode(['id' => 4, 'error' => self::$options['messages']['invalid_id']]);
            return false;
        }

        // Check if the honeypot value is set.
        if (!isset($post[self::CAPTCHA_FIELD_HONEYPOT]) || !empty($post[self::CAPTCHA_FIELD_HONEYPOT])) {
            self::$error = json_encode(['id' => 5, 'error' => self::$options['messages']['invalid_id']]);
            return false;
        }

        // Initialize the session.
        self::tryCreateSession($post[self::CAPTCHA_FIELD_ID]);

        // Check if the selection field is set.
        if (!empty($post[self::CAPTCHA_FIELD_SELECTION]) && is_string($post[self::CAPTCHA_FIELD_SELECTION])) {

            // Parse the selection.
            $selection = explode(',', $post[self::CAPTCHA_FIELD_SELECTION]);
            if(count($selection) === 3) {
                $clickedPosition = self::determineClickedIcon($selection[0], $selection[1], $selection[2], self::$session->icons[2]);
            }

            // If the clicked position matches the stored position, the form can be submitted.
            if (self::$session->completed === true && (isset($clickedPosition) && in_array($clickedPosition, self::$session->positions))) {
                return true;
            } else {
                self::$error = json_encode(['id' => 1, 'error' => self::$options['messages']['wrong_icon']]);
            }
        } else {
            self::$error = json_encode(['id' => 2, 'error' => self::$options['messages']['no_selection']]);
        }

        return false;
    }

    /**
     * Checks and sets the captcha session. If the user selected the
     * correct image, the value will be true, else false.
     *
     * @param array $payload The payload of the HTTP Post request.
     *
     * @return boolean TRUE if the correct image was selected, FALSE if not.
     */
    public static function setSelectedAnswer($payload)
    {
        if (!empty($payload)) {

            // Check if the captcha ID is set.
            if (!isset($payload['i']) || !is_numeric($payload['i'])) {
                return false;
            }

            // Initialize the session.
            self::tryCreateSession($payload['i']);

            // Check if the selection is set and matches the position from the session.
            if (isset($payload['x'], $payload['y'], $payload['w']) &&
                (in_array(self::determineClickedIcon($payload['x'], $payload['y'], $payload['w'], self::$session->icons[2]), self::$session->positions))) {

                self::$session->completed = true;
                self::$session->save();
                return true;
            } else {
                self::$session->completed = false;
                self::$session->save();
            }
        }

        return false;
    }

    /**
     * Generates and displays the captcha icons image.
     *
     * @param int $captchaIdentifier The identifier of the captcha.
     */
    public static function getImage($captchaIdentifier = null)
    {
        // Check if the captcha id is set
        if (isset($captchaIdentifier) && $captchaIdentifier > -1) {

            // Initialize the session.
            self::tryCreateSession($captchaIdentifier);

            // Check the amount of times an icon has been requested
            if (self::$session->requested) {
                header('HTTP/1.1 403 Forbidden');
                exit;
            }

            // Update the request counter.
            self::$session->requested = true;
            self::$session->save();

            $iconsDirectoryPath = $_SESSION[self::ICON_CAPTCHA][self::CAPTCHA_ICON_PATH];
            $placeholder = $iconsDirectoryPath . DIRECTORY_SEPARATOR . 'placeholder.png';

            // Check if the placeholder icon exists.
            if (is_file($placeholder)) {

                // Format the path to the icons directory.
                $iconPath = $iconsDirectoryPath . DIRECTORY_SEPARATOR . self::$session->mode . DIRECTORY_SEPARATOR;

                // Prepare the images.
                $placeholder = imagecreatefrompng($placeholder);
                $correctIcon = imagecreatefrompng($iconPath . 'icon-' . self::$session->icons[1] . '.png');
                $incorrectIcon = imagecreatefrompng($iconPath . 'icon-' . self::$session->icons[0] . '.png');

                // Prepare the image pixel information.
                $iconCount = self::$session->icons[2];
                $iconSize = self::CAPTCHA_ICON_SIZES[$iconCount];
                $iconOffset = ((self::CAPTCHA_SIZE / $iconCount) - 30) / 2;
                $iconOffsetAdd = (self::CAPTCHA_SIZE / $iconCount) - $iconSize;
                $iconLineSize = self::CAPTCHA_SIZE / $iconCount;

                // Border color TODO make custom
                $borderColor = imagecolorallocate($placeholder, 240, 240, 240);

                // Copy the icons onto the placeholder.
                $xOffset = $iconOffset;
                for($i = 0; $i < $iconCount; $i++) {
                    $icon = in_array($i + 1, self::$session->positions) ? $correctIcon : $incorrectIcon;

                    // Rotate icon.
                    $degree = mt_rand(1, 4);
                    if($degree !== 4) {
                        $icon = imagerotate($icon, $degree * 90, 0);
                    }

                    // Flip icon.
                    if(mt_rand(1, 2) === 1) imageflip($icon, IMG_FLIP_VERTICAL);
                    if(mt_rand(1, 2) === 1) imageflip($icon, IMG_FLIP_HORIZONTAL);

                    // Copy the icon onto the placeholder.
                    imagecopy($placeholder, $icon, ($iconSize * $i) + $xOffset, 10, 0, 0, 30, 30);
                    $xOffset += $iconOffsetAdd;

                    // Add the vertical separator lines to the placeholder (not for first icon).
                    if($i > 0) {
                        imageline($placeholder, $iconLineSize * $i, 0, $iconLineSize * $i, 50, $borderColor);
                    }
                }

                // Set the content type header to the PNG MIME-type.
                header('Content-type: image/png');

                // Disable caching of the image.
                header('Expires: 0');
                header('Cache-Control: no-cache, no-store, must-revalidate');
                header('Cache-Control: post-check=0, pre-check=0', false);
                header('Pragma: no-cache');

                // Show the image and exit the code
                imagepng($placeholder);
                imagedestroy($placeholder);
            }
        }
    }

    /**
     * Invalidates the {@see CaptchaSession} of the given captcha identifier.
     * The data stored inside the session will be destroyed, as the session will be unset.
     *
     * @param int $captchaIdentifier The identifier of the captcha.
     */
    public static function invalidateCaptcha($captchaIdentifier)
    {
        // Unset the previous session data
        self::tryCreateSession($captchaIdentifier);
        self::$session->destroy();
    }

    /**
     * Tries to load or initialize a new {@see CaptchaSession} with the given captcha identifier.
     * When a session is found, it's data will be loaded, else a new session will be created.
     *
     * @param int $captchaIdentifier The identifier of the captcha.
     */
    private static function tryCreateSession($captchaIdentifier = 0)
    {
        // If the session is not loaded yet, load it.
        if (!isset(self::$session)) {
            self::$session = new CaptchaSession($captchaIdentifier);
        }
    }

    /**
     * Returns the clicked icon position based on the X and Y position and the captcha width.
     *
     * @param $clickedXPos int The X position of the click.
     * @param $clickedYPos int The Y position of the click.
     * @param $captchaWidth int The width of the captcha.
     *
     * @return int The selected icon position.
     */
    private static function determineClickedIcon($clickedXPos, $clickedYPos, $captchaWidth, $iconAmount)
    {
        // Check if the clicked position is valid.
        if($clickedXPos < 0 || $clickedXPos > $captchaWidth || $clickedYPos < 0 || $clickedYPos > 50) {
            return -1;
        }
        return (int)ceil($clickedXPos / ($captchaWidth / $iconAmount));
    }
}