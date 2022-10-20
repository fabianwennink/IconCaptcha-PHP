<?php

/**
 * IconCaptcha Plugin: v3.1.0
 * Copyright © 2022, Fabian Wennink (https://www.fabianwennink.nl)
 *
 * Licensed under the MIT license: https://www.fabianwennink.nl/projects/IconCaptcha/license
 */

namespace IconCaptcha;

class IconCaptcha
{
    const CAPTCHA_FIELD_SELECTION = 'ic-hf-se';
    const CAPTCHA_FIELD_ID = 'ic-hf-id';
    const CAPTCHA_FIELD_HONEYPOT = 'ic-hf-hp';
    const CAPTCHA_FIELD_TOKEN = '_iconcaptcha-token';
    const CAPTCHA_IMAGE_SIZE = 320;
    const CAPTCHA_ICONS_FOLDER_COUNT = 180;
    const CAPTCHA_ICON_SIZES = [5 => 50, 6 => 40, 7 => 30, 8 => 20];
    const CAPTCHA_MAX_LOWEST_ICON_COUNT = [5 => 2, 6 => 2, 7 => 3, 8 => 3];
    const CAPTCHA_DEFAULT_BORDER_COLOR = [240, 240, 240];

    /**
     * @var string A JSON encoded error message, which will be shown to the user.
     */
    private $error;

    /**
     * @var IconCaptchaSessionInterface The session containing captcha information.
     */
    private $session;

    /**
     * @var mixed Default values for all the server-side options.
     */
    private $options;

    public function __construct($options = [])
    {
        $this->options = IconCaptchaOptions::prepare($options);
    }

    /**
     * Overwrite the options for the captcha. The given options will be merged together with the
     * default options and overwrite the default values. If any of the options are missing
     * in the given options array, they will be set with their default value.
     * @param array $options The array of options.
     */
    public function options($options)
    {
        $this->options = IconCaptchaOptions::prepare($options);
    }

    /**
     * @return IconCaptchaRequest
     */
    public function request()
    {
        return new IconCaptchaRequest($this);
    }

    /**
     * Returns the validation error message, or return NULL if there is no error.
     *
     * @return string|null The JSON encoded error message containing the error ID and message, or NULL.
     */
    public function getErrorMessage()
    {
        return !empty($this->error) ? json_decode($this->error)->error : null;
    }

    /**
     * Initializes the state of a captcha. The amount of icons shown in the captcha image, their positions,
     * which icon is correct and which icon identifiers should be used will all be determined in this function.
     * This information will be stored a captcha session, implementing {@see IconCaptchaSessionInterface}.
     * The details required to initialize the client will be returned as a base64 encoded JSON string.
     *
     * In case a timeout is detected, no state will be initialized and an error message
     * will be returned, also as a base64 encoded JSON string.
     *
     * @param string $theme The theme of the captcha.
     * @param int $identifier The identifier of the captcha.
     *
     * @return string Captcha details required to initialize the client.
     */
    public function getCaptchaData($theme, $identifier)
    {
        // Set the captcha id property
        $this->createSession($identifier);

        // Check if the max attempts limit has been reached and a timeout is active.
        // If reached, return an error and the remaining time.
        if ($this->session->attemptsTimeout > 0) {
            if (time() <= $this->session->attemptsTimeout) {
                return base64_encode(json_encode([
                    'error' => 1, 'data' => ($this->session->attemptsTimeout - time()) * 1000 // remaining time.
                ]));
            } else {
                $this->session->attemptsTimeout = 0;
                $this->session->attempts = 0;
            }
        }

        $minIconAmount = $this->options['image']['amount']['min'];
        $maxIconAmount = $this->options['image']['amount']['max'];

        // Determine the number of icons to add to the image.
        $iconAmount = $minIconAmount;
        if ($minIconAmount !== $maxIconAmount) {
            $iconAmount = mt_rand($minIconAmount, $maxIconAmount);
        }

        // Number of times the correct image will be placed onto the placeholder.
        $correctIconAmount = mt_rand(1, self::CAPTCHA_MAX_LOWEST_ICON_COUNT[$iconAmount]);
        $totalIconAmount = $this->calculateIconAmounts($iconAmount, $correctIconAmount);
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
            $tempIconId = mt_rand(1, self::CAPTCHA_ICONS_FOLDER_COUNT);
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
        $attemptsCount = $this->session->attempts;

        // Unset the previous session data.
        $this->session->clear();

        // Set the chosen icons and position and reset the requested status.
        $this->session->mode = $theme;
        $this->session->icons = $iconPositions;
        $this->session->iconIds = $iconIds;
        $this->session->correctId = $correctIconId;
        $this->session->requested = false;
        $this->session->attempts = $attemptsCount;
        $this->session->save();

        // Return the captcha details.
        return base64_encode(json_encode([
            'id' => $identifier
        ]));
    }

    /**
     * Checks if the by the user selected icon is the correct icon. Whether the clicked icon is correct or not
     * will be determined based on the clicked X and Y coordinates and the width of the IconCaptcha DOM element.
     *
     * If the selected icon is indeed the correct icon, the captcha session linked to the captcha identifier
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
    public function makeSelection($payload)
    {
        if (!empty($payload)) {

            // Check if the captcha ID and required other payload data is set.
            if (!isset($payload['i'], $payload['x'], $payload['y'], $payload['w'])) {
                return false;
            }

            // Initialize the session.
            $this->createSession($payload['i']);

            // Get the clicked position.
            $clickedPosition = $this->determineClickedIcon($payload['x'], $payload['y'], $payload['w'], count($this->session->icons));

            // Check if the selection is set and matches the position from the session.
            if ($this->session->icons[$clickedPosition] === $this->session->correctId) {
                $this->session->attempts = 0;
                $this->session->attemptsTimeout = 0;
                $this->session->completed = true;
                $this->session->save();

                return true;
            } else {
                $this->session->completed = false;

                // Increase the attempts counter.
                $this->session->attempts += 1;

                // If the max amount has been reached, set a timeout (if set).
                if ($this->session->attempts === $this->options['attempts']['amount']
                    && $this->options['attempts']['timeout'] > 0) {
                    $this->session->attemptsTimeout = time() + $this->options['attempts']['timeout'];
                }

                $this->session->save();
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
    public function getChallenge($identifier = null)
    {
        // Check if the captcha id is set
        if (isset($identifier) && $identifier > -1) {

            // Initialize the session.
            $this->createSession($identifier);

            // Check the amount of times an icon has been requested
            if ($this->session->requested) {
                header('HTTP/1.1 403 Forbidden');
                exit;
            }

            $this->session->requested = true;
            $this->session->save();

            $iconsDirectoryPath = $this->options['iconPath'];
            $placeholder = $iconsDirectoryPath . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'placeholder.png';

            // Check if the placeholder icon exists.
            if (is_file($placeholder)) {

                // Format the path to the icon directory.
                $themeIconColor = $this->options['themes'][$this->session->mode]['icons'];
                $iconPath = $iconsDirectoryPath . DIRECTORY_SEPARATOR . $themeIconColor . DIRECTORY_SEPARATOR;

                // Generate the captcha image.
                $generatedImage = $this->generateChallengeImage($iconPath, $placeholder);

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
     * Validates the user form submission. If the captcha is incorrect, it
     * will set the global error variable and return FALSE, else TRUE.
     *
     * @param array $request The HTTP POST request variable ($_POST).
     *
     * @return boolean TRUE if the captcha was correct, FALSE if not.
     */
    public function validate($request)
    {
        // Make sure the form data is set.
        if (empty($request)) {
            $this->setErrorMessage(3, $this->options['messages']['empty_form']);
            return false;
        }

        // Check if the captcha ID is set.
        if (!isset($request[self::CAPTCHA_FIELD_ID]) || !is_numeric($request[self::CAPTCHA_FIELD_ID])
            || !$this->options['session']::exists($request[self::CAPTCHA_FIELD_ID])) {
            $this->setErrorMessage(4, $this->options['messages']['invalid_id']);
            return false;
        }

        // Check if the honeypot value is set.
        if (!isset($request[self::CAPTCHA_FIELD_HONEYPOT]) || !empty($request[self::CAPTCHA_FIELD_HONEYPOT])) {
            $this->setErrorMessage(5, $this->options['messages']['invalid_id']);
            return false;
        }

        // Verify if the captcha token is correct.
        // TODO check missing for token validation.
        $token = (isset($request[self::CAPTCHA_FIELD_TOKEN])) ? $request[self::CAPTCHA_FIELD_TOKEN] : null;
        if (!$this->validateToken($token)) {
            $this->setErrorMessage(6, $this->options['messages']['form_token']);
            return false;
        }

        // Get the captcha identifier.
        $identifier = $request[self::CAPTCHA_FIELD_ID];

        // Initialize the session.
        $this->createSession($identifier);

        // Check if the selection field is set.
        if (!empty($request[self::CAPTCHA_FIELD_SELECTION]) && is_string($request[self::CAPTCHA_FIELD_SELECTION])) {

            // Parse the selection.
            $selection = explode(',', $request[self::CAPTCHA_FIELD_SELECTION]);
            if (count($selection) === 3) {
                $clickedPosition = $this->determineClickedIcon($selection[0], $selection[1], $selection[2], count($this->session->icons));
            }

            // If the clicked position matches the stored position, the form can be submitted.
            if ($this->session->completed === true &&
                (isset($clickedPosition) && $this->session->icons[$clickedPosition] === $this->session->correctId)) {

                // Invalidate the captcha to prevent resubmission of a form on the same captcha.
                $this->invalidate($identifier);
                return true;
            } else {
                $this->setErrorMessage(1, $this->options['messages']['wrong_icon']);
            }
        } else {
            $this->setErrorMessage(2, $this->options['messages']['no_selection']);
        }

        return false;
    }

    /**
     * Invalidates the captcha session linked to the given captcha identifier.
     * The data stored inside the session will be destroyed, as the session will be unset.
     *
     * @param int $identifier The identifier of the captcha.
     */
    public function invalidate($identifier)
    {
        // Unset the previous session data
        $this->createSession($identifier);
        $this->session->destroy();
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
    public function validateToken($payloadToken, $headerToken = null)
    {
        // Only validate if the token option is enabled.
        if (!empty($this->options['token'])) {
            return (new $this->options['token'])->validate($payloadToken, $headerToken);
        }
        return true;
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
    private function generateChallengeImage($iconPath, $placeholderPath)
    {
        // Prepare the placeholder image.
        $placeholder = imagecreatefrompng($placeholderPath);

        // Prepare the icon images.
        $iconImages = [];
        foreach ($this->session->iconIds as $id) {
            $iconImages[$id] = imagecreatefrompng($iconPath . 'icon-' . $id . '.png');
        }

        // Image pixel information.
        $iconCount = count($this->session->icons);
        $iconSize = self::CAPTCHA_ICON_SIZES[$iconCount];
        $iconOffset = ((self::CAPTCHA_IMAGE_SIZE / $iconCount) - 30) / 2;
        $iconOffsetAdd = (self::CAPTCHA_IMAGE_SIZE / $iconCount) - $iconSize;
        $iconLineSize = self::CAPTCHA_IMAGE_SIZE / $iconCount;

        // Options.
        $rotateEnabled = $this->options['image']['rotate'];
        $flipHorizontally = $this->options['image']['flip']['horizontally'];
        $flipVertically = $this->options['image']['flip']['vertically'];
        $borderEnabled = $this->options['image']['border'];

        // Create the border color, if enabled.
        if ($borderEnabled) {

            // Determine border color.
            if (key_exists($this->session->mode, $this->options['themes'])
                && count($this->options['themes'][$this->session->mode]['color']) === 3) {
                $color = $this->options['themes'][$this->session->mode]['color'];
            } else {
                $color = self::CAPTCHA_DEFAULT_BORDER_COLOR;
            }

            $borderColor = imagecolorallocate($placeholder, $color[0], $color[1], $color[2]);
        }

        // Copy the icons onto the placeholder.
        $xOffset = $iconOffset;
        for ($i = 0; $i < $iconCount; $i++) {

            // Get the icon image from the array. Use position to get the icon ID.
            $icon = $iconImages[$this->session->icons[$i + 1]];

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
     * Returns the clicked icon position based on the X and Y position and the captcha width.
     *
     * @param $clickedXPos int The X position of the click.
     * @param $clickedYPos int The Y position of the click.
     * @param $captchaWidth int The width of the captcha.
     *
     * @return int The selected icon position.
     */
    private function determineClickedIcon($clickedXPos, $clickedYPos, $captchaWidth, $iconAmount)
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
    private function calculateIconAmounts($iconCount, $smallestIconCount = 1)
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
     * Tries to load/initialize a captcha session with the given captcha identifier.
     * When an existing session is found, it's data will be loaded, else a new session will be created.
     *
     * @param int $identifier The identifier of the captcha.
     */
    private function createSession($identifier = 0)
    {
        // Load the captcha session for the current identifier.
        $this->session = new $this->options['session']($identifier);
    }

    /**
     * Sets the global {@see $error} property, which can be retrieved with the {@see getErrorMessage} function.
     * @param int $id The identifier of the error message.
     * @param string $message The error message to set.
     */
    private function setErrorMessage($id, $message)
    {
        $this->error = json_encode(['id' => $id, 'error' => $message]);
    }
}
