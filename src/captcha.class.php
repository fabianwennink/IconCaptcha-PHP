<?php

/**
 * IconCaptcha Plugin: v3.0.0
 * Copyright © 2021, Fabian Wennink (https://www.fabianwennink.nl)
 *
 * Licensed under the MIT license: http://www.opensource.org/licenses/mit-license.php
 */

namespace IconCaptcha;

class IconCaptcha
{
    const SESSION_NAME = 'icon_captcha';
    const CAPTCHA_ICON_PATH = 'icon_path';
    const CAPTCHA_FIELD_SELECTION = 'ic-hf-se';
    const CAPTCHA_FIELD_ID = 'ic-hf-id';
    const CAPTCHA_FIELD_HONEYPOT = 'ic-hf-hp';
    const CAPTCHA_IMAGE_SIZE = 320;
    const CAPTCHA_ICONS_FOLDER_COUNT = 91;
    const CAPTCHA_ICON_SIZES = [5 => 50, 6 => 40, 7 => 30, 8 => 20];
    const CAPTCHA_MAX_LOWEST_ICON_COUNT = [5 => 2, 6 => 2, 7 => 3, 8 => 3];
    const CAPTCHA_DEFAULT_BORDER_COLOR = [240, 240, 240];
    const CAPTCHA_DEFAULT_THEME_BORDER_COLORS = [
        'light' => ['icons' => 'dark', 'color' => self::CAPTCHA_DEFAULT_BORDER_COLOR],
        'legacy-light' => ['icons' => 'dark', 'color' => self::CAPTCHA_DEFAULT_BORDER_COLOR],
        'dark' => ['icons' => 'light', 'color' => [64, 64, 64]],
        'legacy-dark' => ['icons' => 'light', 'color' => [64, 64, 64]],
    ];

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
        'themes' => [],
        'messages' => [
            'wrong_icon' => 'You\'ve selected the wrong image.',
            'no_selection' => 'No image has been selected.',
            'empty_form' => 'You\'ve not submitted any form.',
            'invalid_id' => 'The captcha ID was invalid.'
        ],
        'image' => [
            'amount' => [ // min & max can be 5 - 8
                'min' => 5,
                'max' => 8
            ],
            'rotate' => true,
            'flip' => [
                'horizontally' => true,
                'vertically' => true,
            ],
            'border' => true
        ],
        'attempts' => [
            'amount' => 3,
            'timeout' => 60 // seconds.
        ]
    ];

    /**
     * Set the options for the captcha.
     * @param array $options The array of options.
     */
    public static function options($options)
    {
        // Merge the given options and default options together.
        self::$options = array_merge(self::$options, $options);

        // Update the icon path string.
        self::$options['iconPath'] = (is_string(self::$options['iconPath'])) ? rtrim(self::$options['iconPath'], '/') : '';

        self::$options['image']['amount']['min'] = (is_int(self::$options['image']['amount']['min'])) ? self::$options['image']['amount']['min'] : 5;
        self::$options['image']['amount']['max'] = (is_int(self::$options['image']['amount']['max'])) ? self::$options['image']['amount']['max'] : 8;

        // TODO Save options to session, fetch when needed.

        // TODO Remove this when options are saved in session.
        $_SESSION[self::SESSION_NAME][self::CAPTCHA_ICON_PATH] = self::$options['iconPath'];
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
        self::createSession($captchaIdentifier);

        // Check if the max attempts limit has been reached and a timeout is active.
        // If reached, return an error and the remaining time.
        if(self::$session->attemptsTimeout > 0) {
            if(time() <= self::$session->attemptsTimeout) {
                return base64_encode(json_encode([
                    'error' => 1,
                    'data' => self::$session->attemptsTimeout - time() // remaining time in milliseconds.
                ]));
            } else {
                self::$session->attemptsTimeout = 0;
                self::$session->attempts = 0;
            }
        }

        $minIconAmount = self::$options['image']['amount']['min'];
        $maxIconAmount = self::$options['image']['amount']['max'];

        // Determine the number of icons to add to the image.
        $iconAmount = $minIconAmount;
        if($minIconAmount !== $maxIconAmount) {
            $iconAmount = mt_rand($minIconAmount, $maxIconAmount);
        }

        // Number of times the correct image will be placed onto the placeholder.
        $correctIconAmount = mt_rand(1, self::CAPTCHA_MAX_LOWEST_ICON_COUNT[$iconAmount]);
        $incorrectIconAmounts = self::calculateIconAmounts($iconAmount, $correctIconAmount);

        // At which position(s) the correct image will be placed onto the placeholder.
        $iconPositions = [];
        for ($i = 0; $i < $correctIconAmount; $i++) {
            $iconPositions[] = mt_rand(1, $iconAmount);
        }

        // Get a random number (correct image)
        $correctIconId = mt_rand(1, self::CAPTCHA_ICONS_FOLDER_COUNT);
        $incorrectIconIds = [];

        // Pick random number(s) for the incorrect icon(s).
        // Loop until a number is found which doesn't match the correct icon ID.
        while (count($incorrectIconIds) < count($incorrectIconAmounts)) {
            $tempIncorrectIconId = mt_rand(1, self::CAPTCHA_ICONS_FOLDER_COUNT);
            if ($tempIncorrectIconId !== $correctIconId) {
                $incorrectIconIds[] = $tempIncorrectIconId;
            }
        }

        // Get the last attempts count to restore, after clearing the session.
        $attemptsCount = self::$session->attempts;

        // Unset the previous session data.
        self::$session->clear();

        // Set the chosen icons and position and reset the requested status.
        self::$session->mode = $theme;
        self::$session->icons = [$correctIconId, $incorrectIconIds, $iconAmount, $incorrectIconAmounts];
        self::$session->positions = $iconPositions;
        self::$session->requested = false;
        self::$session->attempts = $attemptsCount;
        self::$session->save();

        // Return the captcha details.
        return base64_encode(json_encode([
            'icons' => $iconAmount
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
        self::createSession($post[self::CAPTCHA_FIELD_ID]);

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
            self::createSession($payload['i']);

            // Check if the selection is set and matches the position from the session.
            if (isset($payload['x'], $payload['y'], $payload['w']) &&
                (in_array(self::determineClickedIcon($payload['x'], $payload['y'], $payload['w'], self::$session->icons[2]), self::$session->positions))) {

                self::$session->completed = true;
                self::$session->save();

                return true;
            } else {
                self::$session->completed = false;

                // Increase the attempts counter.
                // If the max amount has been reached, set a timeout (if set).
                self::$session->attempts = self::$session->attempts + 1;

                if(self::$session->attempts === self::$options['attempts']['amount']
                    && self::$options['attempts']['timeout'] > 0) {
                    self::$session->attemptsTimeout = time() + self::$options['attempts']['timeout'];
                }

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
            self::createSession($captchaIdentifier);

            // Check the amount of times an icon has been requested
            if (self::$session->requested) {
                header('HTTP/1.1 403 Forbidden');
                exit;
            }

            self::$session->requested = true;
            self::$session->save();

            $iconsDirectoryPath = $_SESSION[self::SESSION_NAME][self::CAPTCHA_ICON_PATH];
            $placeholder = $iconsDirectoryPath . DIRECTORY_SEPARATOR . 'placeholder.png';

            // Check if the placeholder icon exists.
            if (is_file($placeholder)) {

                // Format the path to the icons directory.
                $iconPath = $iconsDirectoryPath . DIRECTORY_SEPARATOR . self::$session->mode . DIRECTORY_SEPARATOR;

                // Generate the captcha image.
                $generatedImage = self::generateImage($iconPath, $placeholder);

                // Set the content type header to the PNG MIME-type.
                header('Content-type: image/png');

                // Disable caching of the image.
                header('Expires: 0');
                header('Cache-Control: no-cache, no-store, must-revalidate');
                header('Cache-Control: post-check=0, pre-check=0', false);
                header('Pragma: no-cache');

                // Show the image and exit the code
                imagepng($generatedImage);
                imagedestroy($generatedImage);
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
        self::createSession($captchaIdentifier);
        self::$session->destroy();
    }

    private static function generateImage($iconPath, $placeholderPath)
    {
        // Prepare the placeholder and correct/incorrect images.
        $placeholder = imagecreatefrompng($placeholderPath);
        $correctIcon = imagecreatefrompng($iconPath . 'icon-' . self::$session->icons[0] . '.png');

        // TODO
        $incorrectIcons = [];
        foreach (self::$session->icons[1] as $iconId) {
            $incorrectIcons[] = imagecreatefrompng($iconPath . 'icon-' . $iconId . '.png');
        }

        // Prepare the image pixel information.
        $iconCount = self::$session->icons[2];
        $iconSize = self::CAPTCHA_ICON_SIZES[$iconCount];
        $iconOffset = ((self::CAPTCHA_IMAGE_SIZE / $iconCount) - 30) / 2;
        $iconOffsetAdd = (self::CAPTCHA_IMAGE_SIZE / $iconCount) - $iconSize;
        $iconLineSize = self::CAPTCHA_IMAGE_SIZE / $iconCount;

        // Options
        $rotateEnabled = self::$options['image']['rotate'];
        $flipHorizontally = self::$options['image']['flip']['horizontally'];
        $flipVertically = self::$options['image']['flip']['vertically'];
        $borderEnabled = self::$options['image']['border'];

        // Create the border color.
        if($borderEnabled) {

            // Determine border color.
            if(key_exists(self::$session->mode, self::CAPTCHA_DEFAULT_THEME_BORDER_COLORS) && count(self::CAPTCHA_DEFAULT_THEME_BORDER_COLORS[self::$session->mode]['color']) === 3) {
                $color = self::CAPTCHA_DEFAULT_THEME_BORDER_COLORS[self::$session->mode]['color'];
            } else {
                $color = self::CAPTCHA_DEFAULT_BORDER_COLOR;
            }

            // TODO Use this code when options are saved to session.
//        if(key_exists(self::$session->mode, self::$options['themes']) && count(self::$options['themes'][self::$session->mode]['color']) === 3) {
//            $color = self::$options['themes'][self::$session->mode]['color'];
//        } else {
//            $color = self::CAPTCHA_DEFAULT_BORDER_COLOR;
//        }

            $borderColor = imagecolorallocate($placeholder, $color[0], $color[1], $color[2]);
        }

        // Copy the icons onto the placeholder.
        $xOffset = $iconOffset;
        for($i = 0; $i < $iconCount; $i++) {

            // Determine which icon should be used for the current position.
            if(in_array($i + 1, self::$session->positions)) {
                $icon = $correctIcon;
            } else {
                // TODO
                $icon = $incorrectIcons[0];
            }

            // Rotate icon, if enabled.
            if($rotateEnabled) {
                $degree = mt_rand(1, 4);
                if ($degree !== 4) {
                    $icon = imagerotate($icon, $degree * 90, 0);
                }
            }

            // Flip icon, if enabled.
            if($flipHorizontally && mt_rand(1, 2) === 1) imageflip($icon, IMG_FLIP_HORIZONTAL);
            if($flipVertically && mt_rand(1, 2) === 1) imageflip($icon, IMG_FLIP_VERTICAL);

            // Copy the icon onto the placeholder.
            imagecopy($placeholder, $icon, ($iconSize * $i) + $xOffset, 10, 0, 0, 30, 30);
            $xOffset += $iconOffsetAdd;

            // Add the vertical separator lines to the placeholder, if enabled.
            if($borderEnabled && $i > 0) {
                imageline($placeholder, $iconLineSize * $i, 0, $iconLineSize * $i, 50, $borderColor);
            }
        }

        return $placeholder;
    }

    /**
     * Tries to load or initialize a new {@see CaptchaSession} with the given captcha identifier.
     * When a session is found, it's data will be loaded, else a new session will be created.
     *
     * @param int $captchaIdentifier The identifier of the captcha.
     */
    private static function createSession($captchaIdentifier = 0)
    {
        self::$session = new CaptchaSession($captchaIdentifier);
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

    public static function calculateIconAmounts($iconCount, $smallestIconCount = 1)
    {
        $remainder = $iconCount - $smallestIconCount;
        $remainderDivided = $remainder / 2;
        $pickDivided = mt_rand(1, 2) === 1; // 50/50 chance.

        // If division leads to decimal.
        if (fmod($remainderDivided, 1) !== 0.0 && $pickDivided) {
            $left = floor($remainderDivided);
            $right = ceil($remainderDivided);

            // Only return the divided numbers if both are larger than the smallest number.
            if ($left > $smallestIconCount && $right > $smallestIconCount) {
                return [$left, $right];
            }
        } else if($pickDivided === true) { // If no decimals, return the division result.
            return [$remainderDivided, $remainderDivided];
        }

        // Return the whole remainder.
        return [$remainder];
    }
}
