<?php

/*
 * IconCaptcha - Copyright 2023, Fabian Wennink (https://www.fabianwennink.nl)
 * Licensed under the MIT license: https://www.fabianwennink.nl/projects/IconCaptcha/license
 *
 * The above copyright notice and license shall be included in all copies or substantial portions of the software.
 */

namespace IconCaptcha\Challenge;

use Exception;
use IconCaptcha\Attempts\AttemptsFactory;
use IconCaptcha\Attempts\AttemptsInterface;
use IconCaptcha\Challenge\Hooks\Hook;
use IconCaptcha\Challenge\Hooks\InitHookInterface;
use IconCaptcha\Challenge\Hooks\InvalidHookException;
use IconCaptcha\Challenge\Hooks\SelectionHookInterface;
use IconCaptcha\Exceptions\FileNotFoundException;
use IconCaptcha\Payload;
use IconCaptcha\Session\Session;
use IconCaptcha\Session\SessionFactory;
use IconCaptcha\Session\SessionInterface;
use IconCaptcha\Utils;
use JsonException;

class Challenge
{
    /**
     * The maximum height of a challenge image.
     */
    private const MAX_HEIGHT_OF_CHALLENGE = 50;

    /**
     * The lowest amount of different icon possibilities per amount of icons per challenge.
     * @example If there are 6 icons per challenge, the lowest amount of different icons can be 2.
     */
    private const CAPTCHA_MAX_LOWEST_ICON_COUNT = [5 => 2, 6 => 2, 7 => 3, 8 => 3];

    /**
     * @var Session The captcha challenge session.
     */
    private Session $session;

    /**
     * @var AttemptsInterface The attempts/timeout manager instance.
     */
    private AttemptsInterface $attempts;

    /**
     * @var array The captcha options.
     */
    private array $options;

    /**
     * @var mixed The storage container.
     */
    private $storage;

    /**
     * Creates a new challenge processor instance.
     *
     * @param mixed $storage The storage container.
     * @param array $options The captcha options.
     */
    public function __construct($storage, array $options)
    {
        $this->storage = $storage;
        $this->options = $options;
    }

    /**
     * Initializes the challenge manager with the given captcha identifiers.
     *
     * @param string $widgetId The widget identifier.
     * @param string|null $challengeId The challenge identifier.
     */
    public function initialize(string $widgetId, string $challengeId = null): Challenge
    {
        // Get the visitor's current IP address.
        $ipAddress = Utils::getIpAddress($this->options['ipAddress']);

        // Create a new session instance.
        $this->session = SessionFactory::create(
            $this->storage,
            $this->options['session']['driver'] ?? $this->options['storage']['driver'],
            $this->options['session'],
            $ipAddress,
            $widgetId,
            $challengeId
        );

        // Create a new attempts/timeout manager instance.
        $this->attempts = AttemptsFactory::create(
            $this->storage,
            $this->options['validation']['attempts']['storage']['driver'] ?? $this->options['storage']['driver'],
            $this->options['validation']['attempts'],
            $ipAddress
        );

        return $this;
    }

    /**
     * Initializes the state of a captcha. The amount of icons shown in the captcha image, their positions,
     * which icon is correct and which icon identifiers should be used will all be determined in this function.
     * This information will be stored a captcha session, implementing {@see SessionInterface}.
     * The details required to initialize the client will be returned as a base64 encoded JSON string.
     *
     * In case a timeout is detected, no state will be initialized and an error message will be
     * returned instead. This error message will also be a base64 encoded JSON string.
     *
     * @param string $theme The theme of the captcha.
     * @return string Captcha details required to initialize the client.
     * @throws JsonException If a problem occurs when encoding the response payload.
     * @throws InvalidHookException If an attempt was made to call a hook, but failed as it was configured incorrectly.
     * @throws Exception If an unexpected error occurred while generating a challenge.
     */
    public function generate(string $theme): string
    {
        // Call the init 'autocomplete' hook, if provided.
        $shouldImmediatelyComplete = Hook::call(
            'init', InitHookInterface::class, 'shouldImmediatelyComplete',
            $this->session, $this->options, false
        );

        // If the hook returned TRUE, the challenge should autocomplete.
        if ($shouldImmediatelyComplete) {
            $this->markChallengeCompleted();
            return $this->getCompletionPayload();
        }

        // Check if the max attempts limit has been reached and a timeout is active.
        // If reached, return an error and the remaining time.
        if ($this->attempts->isEnabled() && $this->attempts->isTimeoutActive()) {
            return Payload::encode([
                'error' => 'too-many-attempts',
                'data' => $this->attempts->getTimeoutRemainingTime() * 1000
            ]);
        }

        $minIconAmount = $this->options['challenge']['iconAmount']['min'];
        $maxIconAmount = $this->options['challenge']['iconAmount']['max'];
        $availableIconAmount = $this->options['challenge']['availableIcons'];

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

        // Save the challenge data to the session.
        $this->session->clear();
        $this->session->mode = $theme;
        $this->session->icons = $iconPositions;
        $this->session->correctId = $correctIconId;

        // If enabled, set the expiration timestamp for the challenge.
        if ($this->options['validation']['inactivityExpiration'] > 0) {
            $this->session->expiresAt = Utils::getCurrentTimeInMilliseconds()
                + ($this->options['validation']['inactivityExpiration'] * 1000);
        }

        $this->session->save();

        // Return the captcha details.
        return Payload::encode([
            'identifier' => $this->session->getChallengeId(),
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
     * @return string The request payload, containing the completion status.
     * @throws JsonException If a problem occurs when encoding the response payload.
     * @throws InvalidHookException If an attempt was made to call a hook, but failed as it was configured incorrectly.
     */
    public function makeSelection(int $x, int $y, int $width): string
    {
        // Get the clicked position.
        $clickedPosition = $this->determineClickedIcon($x, $y, $width, count($this->session->icons));

        // Check if the selection matches the position from the session.
        if ($this->session->icons[$clickedPosition] === $this->session->correctId) {

            // Mark the challenge as completed.
            $this->markChallengeCompleted();

            // Clear the attempts history of the visitor.
            if ($this->attempts->isEnabled()) {
                $this->attempts->clearAttempts();
            }

            // Call the 'correct selection' hook, if provided.
            Hook::callVoid(
                'selection', SelectionHookInterface::class, 'correct',
                $this->session, $this->options
            );

            return $this->getCompletionPayload();
        }

        // Incorrect icon was clicked, mark as 'not completed'.
        $this->session->completed = false;

        // Increase the attempts counter.
        if ($this->attempts->isEnabled()) {
            $this->attempts->increaseAttempts();
        }

        $this->session->save();

        // Call the 'incorrect selection' hook, if provided.
        Hook::callVoid(
            'selection', SelectionHookInterface::class, 'incorrect',
            $this->session, $this->options
        );

        return Payload::encode([
            'identifier' => $this->session->getChallengeId(),
            'completed' => false,
        ]);
    }

    /**
     * Displays an image containing multiple icons in a random order for the current captcha instance, linked
     * to the given captcha identifier. Headers will be set to prevent caching of the image. In case the captcha
     * image was already requested once, an HTTP status '403 Forbidden' will be set and no image will be returned.
     *
     * The image will only be rendered once as a PNG, and be destroyed right after rendering.
     * @throws FileNotFoundException If the placeholder image does not exist at the configured path.
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
        $placeholderPath = $iconsDirectoryPath . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'placeholder.png';
        $placeholder = realpath($placeholderPath);

        // Check if the placeholder icon exists.
        if ($placeholder === false) {
            throw new FileNotFoundException($placeholderPath);
        }

        // Format the path to the icon directory.
        $themeIconColor = $this->options['themes'][$this->session->mode]['iconStyle'];
        $iconPath = $iconsDirectoryPath . DIRECTORY_SEPARATOR . $themeIconColor . DIRECTORY_SEPARATOR;

        // Instantiate the challenge image generator.
        $imageGenerator = new $this->options['challenge']['generator']($this->session, $this->options);

        // Generate and render the challenge.
        return $imageGenerator->render(
            $imageGenerator->generate($iconPath, $placeholder)
        );
    }

    /**
     * Marks the challenge as completed.
     */
    private function markChallengeCompleted(): void
    {
        $this->session->clear();
        $this->session->completed = true;
        $this->session->requested = true;

        // If enabled, set the expiration timestamp for the completed captcha.
        if ($this->options['validation']['completionExpiration'] > 0) {
            $this->session->expiresAt = Utils::getCurrentTimeInMilliseconds()
                + ($this->options['validation']['completionExpiration'] * 1000);
        }

        $this->session->save();
    }

    /**
     * Returns the request payload for a completed challenge.
     *
     * @return string The payload.
     * @throws JsonException If a problem occurs when encoding the payload.
     */
    private function getCompletionPayload(): string
    {
        return Payload::encode([
            'identifier' => $this->session->getChallengeId(),
            'completed' => true,
            'expiredAt' => $this->session->expiresAt,
        ]);
    }

    /**
     * Returns the clicked icon position based on the X and Y position and the captcha width.
     *
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
     * @throws Exception If a problem occurs during the calculations.
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
