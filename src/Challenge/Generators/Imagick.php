<?php

namespace IconCaptcha\Challenge\Generators;

use IconCaptcha\Challenge\Image\AbstractImageGenerator;

use Imagick as ImagickImage;
use ImagickDraw;
use ImagickPixel;

/**
 * The challenge generator using the ImageMagick image processor library to generate the image.
 * In order to use this generator, the 'imagick' extension must be installed and enabled in the php.ini.
 * @link https://www.php.net/manual/en/book.imagick.php Extension information.
 * @link https://www.php.net/manual/en/imagick.setup.php Extension installation/configuration information.
 */
class Imagick extends AbstractImageGenerator
{
    /**
     * @inheritDoc
     */
    public function loadImage($path)
    {
        $image = new ImagickImage();
        $image->readImage($path);
        return $image;
    }

    /**
     * @inheritDoc
     */
    public function flipHorizontal($image)
    {
        /**
         * @var ImagickImage $image
         */
        $image->flopImage();
        return $image;
    }

    /**
     * @inheritDoc
     */
    public function flipVertical($image)
    {
        /**
         * @var ImagickImage $image
         */
        $image->flipImage();
        return $image;
    }

    /**
     * @inheritDoc
     */
    public function rotate($image, $degree)
    {
        /**
         * @var ImagickImage $image
         */
        $image->rotateImage(new ImagickPixel('white'), $degree);
        return $image;
    }

    /**
     * @inheritDoc
     */
    public function drawBorder($image, $color, $x1, $y1, $x2, $y2)
    {
        $draw = new ImagickDraw();
        $draw->setFillColor($color);
        $draw->setStrokeWidth(1);
        $draw->line($x1, $y1, $x2, $y2);

        /**
         * @var ImagickImage $image
         */
        $image->drawImage($draw);

        return $image;
    }

    /**
     * @inheritDoc
     */
    public function drawIcon($image, $icon, $x, $y, $size)
    {
        /**
         * @var ImagickImage $image
         */
        $image->compositeImage($icon, ImagickImage::COMPOSITE_DEFAULT, $x, $y);
        return $image;
    }

    /**
     * @inheritDoc
     */
    public function colorFromRGB($image, $red, $green, $blue)
    {
        return new ImagickPixel("rgb($red, $green, $blue)");
    }

    /**
     * @inheritDoc
     */
    public function renderImage($image)
    {
        echo $image;
    }
}
