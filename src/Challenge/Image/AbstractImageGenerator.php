<?php

namespace IconCaptcha\Challenge\Image;

use IconCaptcha\Session\Session;

abstract class AbstractImageGenerator implements ImageGeneratorInterface
{
    const CAPTCHA_IMAGE_SIZE = 320;

    const CAPTCHA_ICON_SIZES = [5 => 50, 6 => 40, 7 => 30, 8 => 20];

    const CAPTCHA_DEFAULT_BORDER_COLOR = [240, 240, 240];

    /**
     * @var Session $session
     */
    private $session;

    /**
     * @var array $options
     */
    private $options;

    public function __construct($session, $options)
    {
        $this->session = $session;
        $this->options = $options;
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
    public function generate($iconPath, $placeholderPath)
    {
        // Prepare the placeholder image.
        $placeholder = $this->loadImage($placeholderPath);

        // Prepare the icon images.
        $iconImages = [];
        foreach ($this->session->iconIds as $id) {
            $iconImages[$id] = $this->loadImage(realpath($iconPath . 'icon-' . $id . '.png'));
        }

        // Image pixel information.
        $iconCount = count($this->session->icons);
        $iconSize = self::CAPTCHA_ICON_SIZES[$iconCount];
        $iconOffset = (int)(((self::CAPTCHA_IMAGE_SIZE / $iconCount) - 30) / 2);
        $iconOffsetAdd = (int)((self::CAPTCHA_IMAGE_SIZE / $iconCount) - $iconSize);
        $iconLineSize = (int)(self::CAPTCHA_IMAGE_SIZE / $iconCount);

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

            $borderColor = $this->colorFromRGB($placeholder, $color[0], $color[1], $color[2]);
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
                    $icon = $this->rotate($icon, $degree * 90);
                }
            }

            // Flip icon horizontally, if enabled.
            if ($flipHorizontally && mt_rand(1, 2) === 1) {
                $this->flipHorizontal($icon);
            }

            // Flip icon vertically, if enabled.
            if ($flipVertically && mt_rand(1, 2) === 1) {
                $this->flipVertical($icon);
            }

            // Copy the icon onto the placeholder.
            $this->drawIcon($placeholder, $icon, ($iconSize * $i) + $xOffset, 10, 30);
            $xOffset += $iconOffsetAdd;

            // Add the vertical separator lines to the placeholder, if enabled.
            if ($borderEnabled && $i > 0) {
                $this->drawBorder($placeholder, $borderColor, $iconLineSize * $i, 0, $iconLineSize * $i, 50);
            }
        }

        return $placeholder;
    }

    /**
     * Outputs the given challenge image resource as a base64 string.
     * @param mixed $image The challenge image to render.
     * @return string The image as a base64 string.
     */
    public abstract function render($image);
}
