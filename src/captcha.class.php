<?php

/**
 * IconCaptcha Plugin: v3.1.2
 * Copyright © 2023, Fabian Wennink (https://www.fabianwennink.nl)
 *
 * Licensed under the MIT license: https://www.fabianwennink.nl/projects/IconCaptcha/license
 */

namespace IconCaptcha;

class IconCaptcha
{
    const SESSION_NAME = 'iconcaptcha';
    const SESSION_SETTINGS = 'settings';
    const SESSION_TOKEN = 'csrf';
    const CAPTCHA_FIELD_SELECTION = 'ic-hf-se';
    const CAPTCHA_FIELD_ID = 'ic-hf-id';
    const CAPTCHA_FIELD_HONEYPOT = 'ic-hf-hp';
    const CAPTCHA_FIELD_TOKEN = '_iconcaptcha-token';
    const CAPTCHA_TOKEN_LENGTH = 20;
    const CAPTCHA_IMAGE_SIZE = 320;
    const CAPTCHA_ICON_SIZES = [5 => 50, 6 => 40, 7 => 30, 8 => 20];
    const CAPTCHA_MAX_LOWEST_ICON_COUNT = [5 => 2, 6 => 2, 7 => 3, 8 => 3];
    const CAPTCHA_DEFAULT_BORDER_COLOR = [240, 240, 240];
    const CAPTCHA_DEFAULT_THEME_COLORS = [
        'light' => ['icons' => 'light', 'color' => self::CAPTCHA_DEFAULT_BORDER_COLOR],
        'legacy-light' => ['icons' => 'light', 'color' => self::CAPTCHA_DEFAULT_BORDER_COLOR],
        'dark' => ['icons' => 'dark', 'color' => [64, 64, 64]],
        'legacy-dark' => ['icons' => 'dark', 'color' => [64, 64, 64]],
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
        'themes' => self::CAPTCHA_DEFAULT_THEME_COLORS,
        'messages' => [
            'wrong_icon' => 'You\'ve selected the wrong image.',
            'no_selection' => 'No image has been selected.',
            'empty_form' => 'You\'ve not submitted any form.',
            'invalid_id' => 'The captcha ID was invalid.',
            'form_token' => 'The form token was invalid.'
        ],
        'image' => [
            'availableIcons' => 180,
            'amount' => [
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
            'amount' => 5,
            'timeout' => 30 // seconds.
        ],
        'token' => true
    ];

    /**
     * @var bool TRUE if the default $options variable is updated with custom options, FALSE if not.
     */
    private static $optionsLoaded = false;

    /**
     * Set the options for the captcha. The given options will be merged together with the
     * default options and overwrite the default values. If any of the options are missing
     * in the given options array, they will be set with their default value.
     * @param array $options The array of options.
     */
    public static function options($options)
    {
        // Merge the given options and default options together.
        self::$options = array_replace_recursive(self::$options, $options);

        // Update the icon path string.
        self::$options['iconPath'] = (is_string(self::$options['iconPath'])) ? rtrim(self::$options['iconPath'], '/') : '';

        // Store the settings in the session.
        $_SESSION[self::SESSION_NAME][self::SESSION_SETTINGS] = self::$options;
        self::$optionsLoaded = true;
    }

    /**
     * Generates and returns a secure random string which will serve as a CSRF token for the current session. After
     * generating the token, it will be saved in the global session variable. The length of the token will be
     * determined by the value of the global constant {@see CAPTCHA_TOKEN_LENGTH}. A token will only be generated
     * when no token has been generated before in the current session. If a token already exists, this token will
     * be returned instead.
     *
     * @return string The captcha token.
     */
    public static function token()
    {
        // Make sure to only generate a token if none exists.
        if (!isset($_SESSION[self::SESSION_NAME], $_SESSION[self::SESSION_NAME][self::SESSION_TOKEN])) {

            // Create a secure captcha session token.
            if (function_exists('random_bytes')) {
                // Only available for PHP 7 or higher.
                try {
                    $token = bin2hex(random_bytes(self::CAPTCHA_TOKEN_LENGTH));
                } catch (\Exception $e) {
                    // Using a fallback in case of an exception.
                    $token = str_shuffle(md5(uniqid(rand(), true)));
                }
            } elseif (function_exists('openssl_random_pseudo_bytes')) {
                // Only available when the OpenSSL extension is installed.
                $token = bin2hex(openssl_random_pseudo_bytes(self::CAPTCHA_TOKEN_LENGTH));
            } else {
                // If not on PHP 7+ or having the OpenSSL extension installed, use this fallback.
                $token = str_shuffle(md5(uniqid(rand(), true)));
            }

            $_SESSION[self::SESSION_NAME][self::SESSION_TOKEN] = $token;
        }

        return $_SESSION[self::SESSION_NAME][self::SESSION_TOKEN];
    }

    /**
     * Returns the validation error message, or return NULL if there is no error.
     *
     * @return string|null The JSON encoded error message containing the error ID and message, or NULL.
     */
    public static function getErrorMessage()
    {
        return !empty(self::$error) ? json_decode(self::$error)->error : null;
    }

    /**
     * Initializes the state of a captcha. The amount of icons shown in the captcha image, their positions,
     * which icon is correct and which icon identifiers should be used will all be determined in this function.
     * This information will be stored in the {@see CaptchaSession}. The details required to initialize the client
     * will be returned as a base64 encoded JSON string.
     *
     * In case a timeout is detected, no state will be initialized and an error message
     * will be returned, also as a base64 encoded JSON string.
     *
     * @param string $theme The theme of the captcha.
     * @param int $identifier The identifier of the captcha.
     *
     * @return string Captcha details required to initialize the client.
     */
    public static function getCaptchaData($theme, $identifier)
    {
        // Set the captcha id property
        self::createSession($identifier);

        // Check if the max attempts limit has been reached and a timeout is active.
        // If reached, return an error and the remaining time.
        if (self::$session->attemptsTimeout > 0) {
            if (time() <= self::$session->attemptsTimeout) {
                return base64_encode(json_encode([
                    'error' => 1, 'data' => (self::$session->attemptsTimeout - time()) * 1000 // remaining time.
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
        if ($minIconAmount !== $maxIconAmount) {
            $iconAmount = mt_rand($minIconAmount, $maxIconAmount);
        }

        // Number of times the correct image will be placed onto the placeholder.
        $correctIconAmount = mt_rand(1, self::CAPTCHA_MAX_LOWEST_ICON_COUNT[$iconAmount]);
        $totalIconAmount = self::calculateIconAmounts($iconAmount, $correctIconAmount);
        $totalIconAmount[] = $correctIconAmount;

        // Icon position and ID information.
        $iconPositions = [];
        $iconIds = [];
        $correctIconId = -1;

        // Create a random 'icon position' order.
        $tempPositions = range(1, $iconAmount);
        shuffle($tempPositions);

        // Generate the icon positions/IDs array.
        $i = 0;
        while (count($iconIds) < count($totalIconAmount)) {

            // Generate a random icon ID. If it is not in use yet, process it.
            $tempIconId = mt_rand(1, self::$options['image']['availableIcons']);
            if (!in_array($tempIconId, $iconIds)) {
                $iconIds[] = $tempIconId;

                // Assign the current icon ID to one or more positions.
                for ($j = 0; $j < $totalIconAmount[$i]; $j++) {
                    $tempKey = array_pop($tempPositions);
                    $iconPositions[$tempKey] = $tempIconId;
                }

                // Set the least appearing icon ID as the correct icon ID.
                if ($correctIconId === -1 && min($totalIconAmount) === $totalIconAmount[$i]) {
                    $correctIconId = $tempIconId;
                }

                $i++;
            }
        }

        // Get the last attempts count to restore, after clearing the session.
        $attemptsCount = self::$session->attempts;

        // Unset the previous session data.
        self::$session->clear();

        // Set the chosen icons and position and reset the requested status.
        self::$session->mode = $theme;
        self::$session->icons = $iconPositions;
        self::$session->iconIds = $iconIds;
        self::$session->correctId = $correctIconId;
        self::$session->requested = false;
        self::$session->attempts = $attemptsCount;
        self::$session->save();

        // Return the captcha details.
        return base64_encode(json_encode([
            'id' => $identifier
        ]));
    }

    /**
     * Validates the user form submission. If the captcha is incorrect, it
     * will set the global error variable and return FALSE, else TRUE.
     *
     * @param array $post The HTTP POST request variable ($_POST).
     *
     * @return boolean TRUE if the captcha was correct, FALSE if not.
     */
    public static function validateSubmission($post)
    {
        // Make sure the form data is set.
        if (empty($post)) {
            self::setErrorMessage(3, self::$options['messages']['empty_form']);
            return false;
        }

        // Check if the captcha ID is set.
        if (!isset($post[self::CAPTCHA_FIELD_ID]) || !is_numeric($post[self::CAPTCHA_FIELD_ID])
            || !CaptchaSession::exists(self::SESSION_NAME, $post[self::CAPTCHA_FIELD_ID])) {
            self::setErrorMessage(4, self::$options['messages']['invalid_id']);
            return false;
        }

        // Check if the honeypot value is set.
        if (!isset($post[self::CAPTCHA_FIELD_HONEYPOT]) || !empty($post[self::CAPTCHA_FIELD_HONEYPOT])) {
            self::setErrorMessage(5, self::$options['messages']['invalid_id']);
            return false;
        }

        // Verify if the captcha token is correct.
        $token = (isset($post[self::CAPTCHA_FIELD_TOKEN])) ? $post[self::CAPTCHA_FIELD_TOKEN] : null;
        if (!self::validateToken($token)) {
            self::setErrorMessage(6, self::$options['messages']['form_token']);
            return false;
        }

        // Get the captcha identifier.
        $identifier = $post[self::CAPTCHA_FIELD_ID];

        // Initialize the session.
        self::createSession($identifier);

        // Check if the selection field is set.
        if (!empty($post[self::CAPTCHA_FIELD_SELECTION]) && is_string($post[self::CAPTCHA_FIELD_SELECTION])) {

            // Parse the selection.
            $selection = explode(',', $post[self::CAPTCHA_FIELD_SELECTION]);
            if (count($selection) === 3) {
                $clickedPosition = self::determineClickedIcon($selection[0], $selection[1], $selection[2], count(self::$session->icons));
            }

            // If the clicked position matches the stored position, the form can be submitted.
            if (self::$session->completed === true &&
                (isset($clickedPosition) && self::$session->icons[$clickedPosition] === self::$session->correctId)) {

                // Invalidate the captcha to prevent resubmission of a form on the same captcha.
                self::invalidateSession($identifier);
                return true;
            } else {
                self::setErrorMessage(1, self::$options['messages']['wrong_icon']);
            }
        } else {
            self::setErrorMessage(2, self::$options['messages']['no_selection']);
        }

        return false;
    }

    /**
     * Checks if the by the user selected icon is the correct icon. Whether the clicked icon is correct or not
     * will be determined based on the clicked X and Y coordinates and the width of the IconCaptcha DOM element.
     *
     * If the selected icon is indeed the correct icon, the {@see CaptchaSession} linked to the captcha identifier
     * will be marked as completed and TRUE will be returned. If an incorrect icon was selected, the session will
     * be marked as incomplete, the 'attempts' counter will be incremented by 1 and FALSE will be returned.
     *
     * A check will also take place to see if a timeout should set for the user, based on the options and attempts counter.
     *
     * @param array $payload The payload of the HTTP Post request, containing the captcha identifier, clicked X
     * and X coordinates and the width of the captcha element.
     *
     * @return boolean TRUE if the correct icon was selected, FALSE if not.
     */
    public static function setSelectedAnswer($payload)
    {
        if (!empty($payload)) {

            // Check if the captcha ID and required other payload data is set.
            if (!isset($payload['i'], $payload['x'], $payload['y'], $payload['w'])) {
                return false;
            }

            // Initialize the session.
            self::createSession($payload['i']);

            // Get the clicked position.
            $clickedPosition = self::determineClickedIcon($payload['x'], $payload['y'], $payload['w'], count(self::$session->icons));

            // Check if the selection is set and matches the position from the session.
            if (self::$session->icons[$clickedPosition] === self::$session->correctId) {
                self::$session->attempts = 0;
                self::$session->attemptsTimeout = 0;
                self::$session->completed = true;
                self::$session->save();

                return true;
            } else {
                self::$session->completed = false;

                // Increase the attempts counter.
                self::$session->attempts += 1;

                // If the max amount has been reached, set a timeout (if set).
                if (self::$session->attempts === self::$options['attempts']['amount']
                    && self::$options['attempts']['timeout'] > 0) {
                    self::$session->attemptsTimeout = time() + self::$options['attempts']['timeout'];
                }

                self::$session->save();
            }
        }

        return false;
    }

    /**
     * Displays an image containing multiple icons in a random order for the current captcha instance, linked
     * to the given captcha identifier. Headers will be set to prevent caching of the image. In case the captcha
     * image was already requested once, a HTTP status '403 Forbidden' will be set and no image will be returned.
     *
     * The image will only be rendered once as a PNG, and be destroyed right after rendering.
     *
     * @param int $identifier The identifier of the captcha.
     */
    public static function getImage($identifier = null)
    {
        // Check if the captcha id is set
        if (isset($identifier) && $identifier > -1) {

            // Initialize the session.
            self::createSession($identifier);

            // Check the amount of times an icon has been requested
            if (self::$session->requested) {
                header('HTTP/1.1 403 Forbidden');
                exit;
            }

            self::$session->requested = true;
            self::$session->save();

            $iconsDirectoryPath = self::$options['iconPath'];
            $placeholder = $iconsDirectoryPath . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'placeholder.png';

            // Check if the placeholder icon exists.
            if (is_file($placeholder)) {

                // Format the path to the icon directory.
                $themeIconColor = self::$options['themes'][self::$session->mode]['icons'];
                $iconPath = $iconsDirectoryPath . DIRECTORY_SEPARATOR . $themeIconColor . DIRECTORY_SEPARATOR;

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
     * Returns a generated image containing the icons for the current captcha instance. The icons will be copied
     * onto a placeholder image, located at the $placeholderPath. The icons will be randomly rotated and flipped
     * based on the captcha options.
     *
     * @param string $iconPath The path to the folder holding the icons.
     * @param string $placeholderPath The path to the placeholder image, with the name of the file included.
     * @return false|\GdImage|resource The generated image.
     */
    public static function generateImage($iconPath, $placeholderPath)
    {
        // Prepare the placeholder image.
        $placeholder = imagecreatefrompng($placeholderPath);

        // Prepare the icon images.
        $iconImages = [];
        foreach (self::$session->iconIds as $id) {
            $iconImages[$id] = imagecreatefrompng($iconPath . 'icon-' . $id . '.png');
        }

        // Image pixel information.
        $iconCount = count(self::$session->icons);
        $iconSize = self::CAPTCHA_ICON_SIZES[$iconCount];
        $iconOffset = (int)(((self::CAPTCHA_IMAGE_SIZE / $iconCount) - 30) / 2);
        $iconOffsetAdd = (int)((self::CAPTCHA_IMAGE_SIZE / $iconCount) - $iconSize);
        $iconLineSize = (int)(self::CAPTCHA_IMAGE_SIZE / $iconCount);

        // Options.
        $rotateEnabled = self::$options['image']['rotate'];
        $flipHorizontally = self::$options['image']['flip']['horizontally'];
        $flipVertically = self::$options['image']['flip']['vertically'];
        $borderEnabled = self::$options['image']['border'];

        // Create the border color, if enabled.
        if ($borderEnabled) {

            // Determine border color.
            if (key_exists(self::$session->mode, self::$options['themes'])
                && count(self::$options['themes'][self::$session->mode]['color']) === 3) {
                $color = self::$options['themes'][self::$session->mode]['color'];
            } else {
                $color = self::CAPTCHA_DEFAULT_BORDER_COLOR;
            }

            $borderColor = imagecolorallocate($placeholder, $color[0], $color[1], $color[2]);
        }

        // Copy the icons onto the placeholder.
        $xOffset = $iconOffset;
        for ($i = 0; $i < $iconCount; $i++) {

            // Get the icon image from the array. Use position to get the icon ID.
            $icon = $iconImages[self::$session->icons[$i + 1]];

            // Rotate icon, if enabled.
            if ($rotateEnabled) {
                $degree = mt_rand(1, 4);
                if ($degree !== 4) { // Only if the 'degree' is not the same as what it would already be at.
                    $icon = imagerotate($icon, $degree * 90, 0);
                }
            }

            // Flip icon horizontally, if enabled.
            if ($flipHorizontally && mt_rand(1, 2) === 1) {
                imageflip($icon, IMG_FLIP_HORIZONTAL);
            }

            // Flip icon vertically, if enabled.
            if ($flipVertically && mt_rand(1, 2) === 1) {
                imageflip($icon, IMG_FLIP_VERTICAL);
            }

            // Copy the icon onto the placeholder.
            imagecopy($placeholder, $icon, ($iconSize * $i) + $xOffset, 10, 0, 0, 30, 30);
            $xOffset += $iconOffsetAdd;

            // Add the vertical separator lines to the placeholder, if enabled.
            if ($borderEnabled && $i > 0) {
                imageline($placeholder, $iconLineSize * $i, 0, $iconLineSize * $i, 50, $borderColor);
            }
        }

        return $placeholder;
    }

    /**
     * Invalidates the {@see CaptchaSession} linked to the given captcha identifier.
     * The data stored inside the session will be destroyed, as the session will be unset.
     *
     * @param int $identifier The identifier of the captcha.
     */
    public static function invalidateSession($identifier)
    {
        // Unset the previous session data
        self::createSession($identifier);
        self::$session->destroy();
    }

    /**
     * Tries to load/initialize a {@see CaptchaSession} with the given captcha identifier.
     * When an existing session is found, it's data will be loaded, else a new session will be created.
     *
     * @param int $identifier The identifier of the captcha.
     */
    private static function createSession($identifier = 0)
    {
        // Load the captcha session for the current identifier.
        self::$session = new CaptchaSession(self::SESSION_NAME, $identifier);

        // If the general captcha options haven't been loaded/set, load them from the session.
        self::getOptions();
    }

    /**
     * Validates the global captcha session token against the given payload token and sometimes against a header token
     * as well. All the given tokens must match the global captcha session token to pass the check. This function
     * will only validate the given tokens if the 'token' option is set to TRUE. If the 'token' option is set to anything
     * else other than TRUE, the check will be skipped.
     *
     * @param string $payloadToken The token string received via the HTTP request body.
     * @param string|null $headerToken The token string received via the HTTP request headers. This value is optional,
     * as not every request will contain custom HTTP headers and thus this token should be able to be skipped. Default
     * value is NULL. When the value is set to anything else other than NULL, the given value will be checked against
     * the captcha session token.
     * @return bool TRUE if the captcha session token matches the given tokens or if the token option is disabled,
     * FALSE if the captcha session token does not match the given tokens.
     */
    public static function validateToken($payloadToken, $headerToken = null)
    {
        $options = self::getOptions();

        // Only validate if the token option is enabled.
        if ($options['token'] === true) {
            $sessionToken = self::getToken();

            // If the token is empty but the option is enabled, the token was never requested.
            if (empty($sessionToken)) {
                return false;
            }

            // Validate the payload and header token (if set) against the session token.
            if ($headerToken !== null) {
                return $sessionToken === $payloadToken && $sessionToken === $headerToken;
            } else {
                return $sessionToken === $payloadToken;
            }
        }

        return true;
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
        if ($clickedXPos < 0 || $clickedXPos > $captchaWidth || $clickedYPos < 0 || $clickedYPos > 50) {
            return -1;
        }
        return (int)ceil($clickedXPos / ($captchaWidth / $iconAmount));
    }

    /**
     * Calculates the amount of times 1 or more other icons can be present in the captcha image besides the correct icon.
     * Each other icons should be at least present 1 time more than the correct icon. When calculating the icon
     * amount(s), the remainder of the calculation ($iconCount - $smallestIconCount) will be used.
     *
     * Example 1: When $smallestIconCount is 1, and the $iconCount is 8, the return value can be [3, 4].
     * Example 2: When $smallestIconCount is 2, and the $iconCount is 6, the return value can be [4]. This is because
     * dividing the remainder (4 / 2 = 2) is equal to the $smallestIconCount, which is not possible.
     * Example 3: When the $smallestIconCount is 2, and the $iconCount is 8, the return value will be [3, 3].
     *
     * @param int $iconCount The total amount of icons which will be present in the generated image.
     * @param int $smallestIconCount The amount of times the correct icon will be present in the generated image.
     * @return int[] The number of times an icon should be rendered onto the captcha image. Each value in the returned
     * array represents a new unique icon.
     */
    private static function calculateIconAmounts($iconCount, $smallestIconCount = 1)
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
        } elseif ($pickDivided === true && $remainderDivided > $smallestIconCount) {
            // If no decimals: only return the divided numbers if it is larger than the smallest number.
            return [$remainderDivided, $remainderDivided];
        }

        // Return the whole remainder.
        return [$remainder];
    }

    /**
     * Returns the captcha options array. In case the options are not yet loaded ({@see $optionsLoaded} will be FALSE),
     * an attempt will be made to load the options from the session. When this happens, the {@see $options} property
     * will be set with the options array.
     * @return array The captcha options.
     */
    private static function getOptions()
    {
        if (self::$optionsLoaded === false) {
            self::$options = $_SESSION[self::SESSION_NAME][self::SESSION_SETTINGS];
        }
        return self::$options;
    }

    /**
     * Returns the captcha session/CSRF token.
     * @return string|null A token as a string, or NULL if no token exists.
     */
    private static function getToken()
    {
        if (isset($_SESSION[self::SESSION_NAME], $_SESSION[self::SESSION_NAME][self::SESSION_TOKEN])) {
            return $_SESSION[self::SESSION_NAME][self::SESSION_TOKEN];
        }
        return null;
    }

    /**
     * Sets the global {@see $error} property, which can be retrieved with the {@see getErrorMessage} function.
     * @param int $id The identifier of the error message.
     * @param string $message The error message to set.
     */
    private static function setErrorMessage($id, $message)
    {
        self::$error = json_encode(['id' => $id, 'error' => $message]);
    }
}
