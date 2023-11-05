<?php

/*
 * IconCaptcha - Copyright 2023, Fabian Wennink (https://www.fabianwennink.nl)
 * Licensed under the MIT license: https://www.fabianwennink.nl/projects/IconCaptcha/license
 *
 * The above copyright notice and license shall be included in all copies or substantial portions of the software.
 */

namespace IconCaptcha\Challenge\Generators;

use IconCaptcha\Challenge\Image\AbstractImageGenerator;

/**
 * The challenge generator using the internal GD image processor api to generate the image.
 * In order to use this generator, the 'gd' extension must be enabled in the php.ini.
 * @link https://www.php.net/manual/en/book.image.php Extension information.
 * @link https://www.php.net/manual/en/image.setup.php Extension installation/configuration information.
 */
class GD extends AbstractImageGenerator
{
    /**
     * @inheritDoc
     */
    public function loadImage(string $path)
    {
        return imagecreatefrompng($path);
    }

    /**
     * @inheritDoc
     */
    public function flipHorizontal($image)
    {
        imageflip($image, IMG_FLIP_HORIZONTAL);
        return $image;
    }

    /**
     * @inheritDoc
     */
    public function flipVertical($image)
    {
        imageflip($image, IMG_FLIP_VERTICAL);
        return $image;
    }

    /**
     * @inheritDoc
     */
    public function rotate($image, int $degree)
    {
        return imagerotate($image, $degree, 0);
    }

    /**
     * @inheritDoc
     */
    public function drawBorder($image, $color, int $x1, int $y1, int $x2, int $y2)
    {
        imageline($image, $x1, $y1, $x2, $y2, $color);
        return $image;
    }

    /**
     * @inheritDoc
     */
    public function drawIcon($image, $icon, int $x, int $y, int $size)
    {
        imagecopy($image, $icon, $x, $y, 0, 0, $size, $size);
        return $image;
    }

    /**
     * @inheritDoc
     */
    public function colorFromRGB($image, int $red, int $green, int $blue)
    {
        return imagecolorallocate($image, $red, $green, $blue);
    }

    /**
     * @inheritDoc
     */
    public function render($image): string
    {
        ob_start();
        imagepng($image);
        $output = ob_get_contents();
        imagedestroy($image);
        ob_end_clean();

        return base64_encode($output);
    }
}
