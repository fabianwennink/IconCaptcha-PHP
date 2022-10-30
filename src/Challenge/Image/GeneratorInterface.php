<?php

namespace IconCaptcha\Challenge\Image;

interface GeneratorInterface
{
    /**
     * Returns an image resource/object containing the image at the given path.
     * @param string $path The absolute path pointing to an image.
     * @return mixed The loaded image resource/object.
     */
    public function loadImage($path);

    /**
     * Outputs the given image.
     * @param mixed $image The image to render.
     */
    public function renderImage($image);

    /**
     * Flips the given image horizontally.
     * @param mixed $image The image to flip horizontally.
     * @return mixed The flipped image.
     */
    public function flipHorizontal($image);

    /**
     * Flips the given image vertically.
     * @param mixed $image The image to flip vertically.
     * @return mixed The flipped image.
     */
    public function flipVertical($image);

    /**
     * Rotates the given image.
     * @param mixed $image The challenge image.
     * @param int $degree The number of degrees to rotate the image.
     * @return mixed The rotated image.
     */
    public function rotate($image, $degree);

    /**
     * Draws a line on the given image at the given coordinates.
     * @param mixed $image The challenge image.
     * @param mixed $color The color object to draw the border with.
     * @param int $x1 The x-coordinate for first point.
     * @param int $y1 The y-coordinate for first point.
     * @param int $x2 The x-coordinate for second point.
     * @param int $y2 The y-coordinate for second point.
     * @return mixed The manipulated image.
     */
    public function drawBorder($image, $color, $x1, $y1, $x2, $y2);

    /**
     * Draws the given icon image onto the larger challenge image, at the given coordinates.
     * @param mixed $image The challenge image.
     * @param mixed $icon The icon image to draw onto the challenge image.
     * @param int $x The x-coordinate where the icon will be drawn.
     * @param int $y The y-coordinate where the icon will be drawn.
     * @param int $size The size of the icon.
     * @return mixed The manipulated image.
     */
    public function drawIcon($image, $icon, $x, $y, $size);

    /**
     * Creates a color object. This object will be used by the {@see drawBorder} function.
     * @param mixed $image The challenge image.
     * @param int $red The red value of the RGB color, between 0 and 255.
     * @param int $green The green value of the RGB color, between 0 and 255.
     * @param int $blue The blue value of the RGB color, between 0 and 255.
     * @return mixed The color object.
     */
    public function colorFromRGB($image, $red, $green, $blue);
}
