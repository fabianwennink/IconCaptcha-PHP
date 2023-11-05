<?php

/*
 * IconCaptcha - Copyright 2023, Fabian Wennink (https://www.fabianwennink.nl)
 * Licensed under the MIT license: https://www.fabianwennink.nl/projects/IconCaptcha/license
 *
 * The above copyright notice and license shall be included in all copies or substantial portions of the software.
 */

namespace IconCaptcha\Challenge\Image;

use Exception;
use IconCaptcha\Challenge\Hooks\GenerationHookInterface;
use IconCaptcha\Challenge\Hooks\Hook;
use IconCaptcha\Exceptions\FileNotFoundException;
use IconCaptcha\Session\SessionInterface;

abstract class AbstractImageGenerator implements ImageGeneratorInterface
{
    /**
     * The default captcha challenge image size in pixels.
     */
    private const CAPTCHA_IMAGE_SIZE = 320;

    /**
     * The different sizes in pixels of each icon on the challenge image based on the amount of icons per challenge.
     */
    private const CAPTCHA_ICON_SIZES = [5 => 50, 6 => 40, 7 => 30, 8 => 20];

    /**
     * The default challenge border color as RGB.
     */
    private const CAPTCHA_DEFAULT_BORDER_COLOR = [240, 240, 240];

    /**
     * @var SessionInterface The session containing captcha information
     */
    private SessionInterface $session;

    /**
     * @var array The captcha options.
     */
    private array $options;

    /**
     * Creates a new image generator instance.
     *
     * @param SessionInterface $session The session containing captcha information
     * @param array $options The captcha options.
     */
    public function __construct(SessionInterface $session, array $options)
    {
        $this->session = $session;
        $this->options = $options;
    }

    /**
     * Returns a generated image containing the icons for the current captcha instance. The icons will be copied
     * onto a placeholder image, located at the $placeholderPath path. The icons will be randomly rotated and flipped
     * based on the captcha options.
     *
     * @param string $iconPath The path to the folder holding the icons.
     * @param string $placeholderPath The path to the placeholder image, with the name of the file included.
     * @return false|\GdImage|resource The generated image.
     * @throws FileNotFoundException If an icon image could not be found.
     * @throws Exception If an unexpected error occurred while generating a challenge.
     */
    public function generate(string $iconPath, string $placeholderPath)
    {
        // Prepare the placeholder image.
        $placeholder = $this->loadImage($placeholderPath);

        // Prepare the icon images.
        $iconImages = [];
        foreach (array_unique($this->session->icons) as $id) {
            $iconImage = realpath($iconPath . "icon-$id.png");

            // Verify that the icon image exists.
            if ($iconImage === false) {
                throw new FileNotFoundException($iconPath . "icon-$id.png");
            }

            $iconImages[$id] = $this->loadImage($iconImage);
        }

        // Image pixel information.
        $iconCount = count($this->session->icons);
        $iconSize = self::CAPTCHA_ICON_SIZES[$iconCount];
        $iconOffset = (int)(((self::CAPTCHA_IMAGE_SIZE / $iconCount) - 30) / 2);
        $iconOffsetAdd = (int)((self::CAPTCHA_IMAGE_SIZE / $iconCount) - $iconSize);
        $iconLineSize = (int)(self::CAPTCHA_IMAGE_SIZE / $iconCount);

        // Options.
        $rotateEnabled = $this->options['challenge']['rotate'];
        $flipHorizontally = $this->options['challenge']['flip']['horizontally'];
        $flipVertically = $this->options['challenge']['flip']['vertically'];
        $borderEnabled = $this->options['challenge']['border'];

        // Create the border color, if enabled.
        if ($borderEnabled) {

            // Determine border color.
            if (array_key_exists($this->session->mode, $this->options['themes'])
                && count($this->options['themes'][$this->session->mode]['separatorColor']) === 3) {
                $color = $this->options['themes'][$this->session->mode]['separatorColor'];
            } else {
                $color = self::CAPTCHA_DEFAULT_BORDER_COLOR;
            }

            $separatorColor = $this->colorFromRGB($placeholder, $color[0], $color[1], $color[2]);
        }

        // Copy the icons onto the placeholder.
        $xOffset = $iconOffset;
        for ($i = 0; $i < $iconCount; $i++) {

            // Get the icon image from the array. Use position to get the icon ID.
            $icon = $iconImages[$this->session->icons[$i + 1]];

            // Rotate icon, if enabled.
            if ($rotateEnabled) {
                $degree = random_int(1, 4);

                // Only if the 'degree' is not the same as what it would already be at.
                if ($degree !== 4) {
                    $icon = $this->rotate($icon, $degree * 90);
                }
            }

            // Flip icon horizontally, if enabled.
            if ($flipHorizontally && random_int(1, 2) === 1) {
                $this->flipHorizontal($icon);
            }

            // Flip icon vertically, if enabled.
            if ($flipVertically && random_int(1, 2) === 1) {
                $this->flipVertical($icon);
            }

            // Copy the icon onto the placeholder.
            $this->drawIcon($placeholder, $icon, ($iconSize * $i) + $xOffset, 10, 30);
            $xOffset += $iconOffsetAdd;

            // Add the vertical separator lines to the placeholder, if enabled.
            if ($borderEnabled && $i > 0) {
                $this->drawBorder($placeholder, $separatorColor, $iconLineSize * $i, 0, $iconLineSize * $i, 50);
            }
        }

        // Call the image generation hook, if provided.
        $placeholder = Hook::call(
            'generation', GenerationHookInterface::class, 'generate',
            $this->session, $this->options, $placeholder, $placeholder
        );

        return $placeholder;
    }

    /**
     * Outputs the given challenge image resource as a base64 string.
     *
     * @param mixed $image The challenge image to render.
     * @return string The image as a base64 string.
     */
    abstract public function render($image): string;
}
