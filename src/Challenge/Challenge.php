<?php

namespace IconCaptcha\Challenge;

use IconCaptcha\Challenge\Hooks\Hook;
use IconCaptcha\Challenge\Hooks\InitHookInterface;
use IconCaptcha\Challenge\Hooks\SelectionHookInterface;
use IconCaptcha\Payload;
use IconCaptcha\Session\SessionInterface;
use IconCaptcha\Utils;

class Challenge
{
    private const MAX_HEIGHT_OF_CHALLENGE = 50;

    private const CAPTCHA_MAX_LOWEST_ICON_COUNT = [5 => 2, 6 => 2, 7 => 3, 8 => 3];

    /**
     * @var SessionInterface The session containing captcha information.
     */
    private SessionInterface $session;

    /**
     * @var array $options
     */
    private array $options;

    public function __construct($options)
    {
        $this->options = $options;
    }

    public function initialize(string $widgetId, string $challengeId = null): Challenge
    {
        $this->session = new $this->options['session']($widgetId, $challengeId);
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
     * @param int $latency The request latency, which will be added onto the expiration timestamp.
     *
     * @return string Captcha details required to initialize the client.
     */
    public function generate(string $theme, int $latency): string
    {
        // Call the init 'autocomplete' hook, if provided.
        $shouldImmediatelyComplete = Hook::call(
            'init', InitHookInterface::class, 'shouldImmediatelyComplete',
            $this->session, $this->options, false
        );

        // If the captcha should autocomplete, update the session and return the success status.
        if($shouldImmediatelyComplete) {
            $this->session->clear();
            $this->session->completed = true;
            $this->session->requested = true;

            // If enabled, set the expiration timestamp for the completed captcha.
            if($this->options['challenge']['completionExpiration'] > 0) {
                $this->session->expiresAt = Utils::getTimeInMilliseconds() + ($this->options['challenge']['completionExpiration'] * 1000) + $latency;
            }

            $this->session->save();

            return Payload::encode([
                'id' => $this->session->getChallengeId(),
                'completed' => true,
                'expiredAt' => $this->session->expiresAt,
            ]);
        }

        // Check if the max attempts limit has been reached and a timeout is active.
        // If reached, return an error and the remaining time.
        // TODO timeout check should be extracted to class method.
        if ($this->session->attemptsTimeout > 0) {
            $currentTimestamp = Utils::getTimeInMilliseconds();

            if ($currentTimestamp <= $this->session->attemptsTimeout) {
                return Payload::encode([
                    'error' => 'too-many-attempts',
                    'data' => ($this->session->attemptsTimeout - $currentTimestamp) + $latency // remaining time.
                ]);
            }

            $this->session->attemptsTimeout = 0;
            $this->session->attempts = 0;
        }

        $minIconAmount = $this->options['image']['amount']['min'];
        $maxIconAmount = $this->options['image']['amount']['max'];
        $availableIconAmount = $this->options['image']['icons'];

        // Determine the number of icons to add to the image.
        $iconAmount = $minIconAmount;
        if ($minIconAmount !== $maxIconAmount) {
            $iconAmount = random_int($minIconAmount, $maxIconAmount);
        }

        // Number of times the correct image will be placed onto the placeholder.
        $correctIconAmount = random_int(1, self::CAPTCHA_MAX_LOWEST_ICON_COUNT[$iconAmount]);
        $totalIconAmount = $this->calculateIconAmounts($iconAmount, $correctIconAmount);
        $totalIconAmount[] = $correctIconAmount;

        // Icon position and ID information.
        $iconIds = [];
        $iconPositions = [];
        $correctIconId = -1;

        // Create a random 'icon position' order.
        $tempPositions = range(1, $iconAmount);
        shuffle($tempPositions);

        // Generate the icon positions/IDs array.
        $i = 0;
        while (count($iconIds) < count($totalIconAmount)) {

            // Generate a random icon ID. If it is not in use yet, process it.
            $tempIconId = random_int(1, $availableIconAmount);
            if (!in_array($tempIconId, $iconIds, true)) {
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

        // Save the challenge data to the session.
        $this->session->clear();
        $this->session->mode = $theme;
        $this->session->icons = $iconPositions;
        $this->session->correctId = $correctIconId;
        $this->session->attempts = $attemptsCount;

        // If enabled, set the expiration timestamp for the challenge.
        if($this->options['challenge']['inactivityExpiration'] > 0) {
            $this->session->expiresAt = Utils::getTimeInMilliseconds() + ($this->options['challenge']['inactivityExpiration'] * 1000) + $latency;
        }

        $this->session->save();

        // Return the captcha details.
        return Payload::encode([
            'id' => $this->session->getChallengeId(),
            'challenge' => $this->render(),
            'expiredAt' => $this->session->expiresAt,
        ]);
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
     * @param int $latency The request latency, which will be added onto the expiration timestamp.
     * @return string The request payload, containing the completion status.
     */
    public function makeSelection(int $x, int $y, int $width, int $latency): string
    {
        // Get the clicked position.
        $clickedPosition = $this->determineClickedIcon($x, $y, $width, count($this->session->icons));

        // Check if the selection is set and matches the position from the session.
        if ($this->session->icons[$clickedPosition] === $this->session->correctId) {

            // If enabled, set the expiration timestamp for the completed captcha.
            if($this->options['challenge']['completionExpiration'] > 0) {
                $this->session->expiresAt = Utils::getTimeInMilliseconds() + ($this->options['challenge']['completionExpiration'] * 1000) + $latency;
            }

            $this->session->completed = true;
            $this->session->save();

            // Call the 'correct selection' hook, if provided.
            Hook::callVoid(
                'selection', SelectionHookInterface::class, 'correct',
                $this->session, $this->options
            );

            return Payload::encode([
                'id' => $this->session->getChallengeId(),
                'completed' => true,
                'expiredAt' => $this->session->expiresAt,
            ]);
        }

        $this->session->completed = false;

        // Increase the attempts counter.
        $this->session->attempts += 1;

        // If the max amount has been reached, set a timeout (if set).
        if ($this->session->attempts === $this->options['attempts']['amount'] && $this->options['attempts']['timeout'] > 0) {
            $this->session->attemptsTimeout = Utils::getTimeInMilliseconds() + ($this->options['attempts']['timeout'] * 1000) + $latency;
        }

        $this->session->save();

        // Call the 'incorrect selection' hook, if provided.
        Hook::callVoid(
            'selection', SelectionHookInterface::class, 'incorrect',
            $this->session, $this->options
        );

        return Payload::encode([
            'id' => $this->session->getChallengeId(),
            'completed' => false,
        ]);
    }

    /**
     * Displays an image containing multiple icons in a random order for the current captcha instance, linked
     * to the given captcha identifier. Headers will be set to prevent caching of the image. In case the captcha
     * image was already requested once, an HTTP status '403 Forbidden' will be set and no image will be returned.
     *
     * The image will only be rendered once as a PNG, and be destroyed right after rendering.
     */
    public function render(): ?string
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
     * @param $clickedXPos int The X position of the click.
     * @param $clickedYPos int The Y position of the click.
     * @param $captchaWidth int The width of the captcha.
     * @param int $iconAmount int The amount of icons present in the challenge.
     * @return int The selected icon position.
     */
    private function determineClickedIcon(int $clickedXPos, int $clickedYPos, int $captchaWidth, int $iconAmount): int
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
     * @return int[] The number of times an icon should be rendered onto the captcha image. Each value in the returned array represents a new unique icon.
     */
    private function calculateIconAmounts(int $iconCount, int $smallestIconCount = 1): array
    {
        $remainder = $iconCount - $smallestIconCount;
        $remainderDivided = $remainder / 2;
        $pickDivided = random_int(1, 2) === 1; // 50/50 chance.

        // If division leads to decimal.
        if ($pickDivided && fmod($remainderDivided, 1) !== 0.0) {
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
