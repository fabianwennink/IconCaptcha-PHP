<?php

namespace IconCaptcha\Challenge;

use IconCaptcha\Session\SessionInterface;

class Challenge
{
    const MAX_HEIGHT_OF_CHALLENGE = 50;

    const CAPTCHA_MAX_LOWEST_ICON_COUNT = [5 => 2, 6 => 2, 7 => 3, 8 => 3];

    /**
     * @var SessionInterface The session containing captcha information.
     */
    private $session;

    /**
     * @var array $options
     */
    private $options;

    public function __construct($options)
    {
        $this->options = $options;
    }

    public function initialize($identifier)
    {
        $this->session = new $this->options['session']($identifier);
        return $this;
    }

    /**
     * Initializes the state of a captcha. The amount of icons shown in the captcha image, their positions,
     * which icon is correct and which icon identifiers should be used will all be determined in this function.
     * This information will be stored a captcha session, implementing {@see SessionInterface}.
     * The details required to initialize the client will be returned as a base64 encoded JSON string.
     *
     * In case a timeout is detected, no state will be initialized and an error message
     * will be returned, also as a base64 encoded JSON string.
     *
     * @param string $theme The theme of the captcha.
     *
     * @return string Captcha details required to initialize the client.
     */
    public function generate($theme)
    {
        // Check if the max attempts limit has been reached and a timeout is active.
        // If reached, return an error and the remaining time.
        // TODO timeout check should be extracted to class method.
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
        $availableIconAmount = $this->options['image']['icons'];

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
            $tempIconId = mt_rand(1, $availableIconAmount);
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
            'id' => $this->session->getId(),
            'challenge' => $this->render(),
            'timestamp' => time(),
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
     * @param int $x The clicked X-coordinate relative to the image dimensions.
     * @param int $y The clicked Y-coordinate relative to the image dimensions.
     * @param int $width The width of the captcha element.
     * @return boolean TRUE if the correct icon was selected, FALSE if not.
     */
    public function makeSelection($x, $y, $width)
    {
        // Get the clicked position.
        $clickedPosition = $this->determineClickedIcon($x, $y, $width, count($this->session->icons));

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
            if ($this->session->attempts === $this->options['attempts']['amount'] && $this->options['attempts']['timeout'] > 0) {
                $this->session->attemptsTimeout = time() + $this->options['attempts']['timeout'];
            }

            $this->session->save();
        }

        return false;
    }

    /**
     * Displays an image containing multiple icons in a random order for the current captcha instance, linked
     * to the given captcha identifier. Headers will be set to prevent caching of the image. In case the captcha
     * image was already requested once, an HTTP status '403 Forbidden' will be set and no image will be returned.
     *
     * The image will only be rendered once as a PNG, and be destroyed right after rendering.
     */
    public function render()
    {
        // Check the amount of times an icon has been requested
        if ($this->session->requested) {
            http_response_code(403);
            exit;
        }

        $this->session->requested = true;
        $this->session->save();

        $iconsDirectoryPath = $this->options['iconPath'];
        $placeholder = realpath($iconsDirectoryPath . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'placeholder.png');

        // Check if the placeholder icon exists.
        if (is_file($placeholder)) {

            // Format the path to the icon directory.
            $themeIconColor = $this->options['themes'][$this->session->mode]['icons'];
            $iconPath = $iconsDirectoryPath . DIRECTORY_SEPARATOR . $themeIconColor . DIRECTORY_SEPARATOR;

            // Instantiate the challenge image generator.
            $imageGenerator = new $this->options['generator']($this->session, $this->options);

            // Generate and render the challenge.
            return $imageGenerator->render(
                $imageGenerator->generate($iconPath, $placeholder)
            );
        }

        return null;
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
        if ($clickedXPos < 0 || $clickedXPos > $captchaWidth || $clickedYPos < 0 || $clickedYPos > self::MAX_HEIGHT_OF_CHALLENGE) {
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
}