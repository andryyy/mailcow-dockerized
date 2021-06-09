<?php

namespace RobThree\Auth\Providers\Qr;

use BaconQrCode\Writer;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Renderer\RendererStyle\Fill;
use BaconQrCode\Renderer\Color\Rgb;
use BaconQrCode\Renderer\RendererStyle\EyeFill;

use BaconQrCode\Renderer\Image\EpsImageBackEnd;
use BaconQrCode\Renderer\Image\ImageBackEndInterface;
use BaconQrCode\Renderer\Image\ImagickImageBackEnd;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;

class BaconQrCodeProvider implements IQRCodeProvider
{
    private $borderWidth = 4; // default from Bacon QR Code
    private $backgroundColour;
    private $foregroundColour;
    private $format;

    /**
     * Ensure we using the latest Bacon QR Code and specify default options
     *
     * @param int $borderWidth space around the QR code, 4 is the default from Bacon QR Code
     * @param string $backgroundColour hex reference for the background colour
     * @param string $foregroundColour hex reference for the foreground colour
     * @param string $format the desired output, png or svg
     */
    public function __construct($borderWidth = 4, $backgroundColour = '#ffffff', $foregroundColour = '#000000', $format = 'png')
    {
        if (! class_exists(ImagickImageBackEnd::class)) {
            throw new \RuntimeException('Make sure you are using version 2 of Bacon QR Code');
        }

        $this->borderWidth = $borderWidth;
        $this->backgroundColour = $this->handleColour($backgroundColour);
        $this->foregroundColour = $this->handleColour($foregroundColour);
        $this->format = strtolower($format);
    }

    /**
     * Standard functions from IQRCodeProvider
     */

    public function getMimeType()
    {
        switch ($this->format) {
            case 'png':
                return 'image/png';
            case 'gif':
                return 'image/gif';
            case 'jpg':
            case 'jpeg':
                return 'image/jpeg';
            case 'svg':
                return 'image/svg+xml';
            case 'eps':
                return 'application/postscript';
        }

        throw new \RuntimeException(sprintf('Unknown MIME-type: %s', $this->format));
    }

    public function getQRCodeImage($qrText, $size)
    {
        switch ($this->format) {
            case 'svg':
                $backend = new SvgImageBackEnd;
                break;
            case 'eps':
                $backend = new EpsImageBackEnd;
                break;
            default:
                $backend = new ImagickImageBackEnd($this->format);
        }

        $output = $this->getQRCodeByBackend($qrText, $size, $backend);

        if ($this->format == 'svg') {
            $svg = explode("\n", $output);
            return $svg[1];
        }

        return $output;
    }

    /**
     * Abstract QR code generation function
     * providing colour changing support
     */
    private function getQRCodeByBackend($qrText, $size, ImageBackEndInterface $backend)
    {
        $rendererStyleArgs = array($size, $this->borderWidth);

        if (is_array($this->foregroundColour) && is_array($this->backgroundColour)) {
            $rendererStyleArgs = array_merge($rendererStyleArgs, array(
                null,
                null,
                Fill::withForegroundColor(
                    new Rgb(...$this->backgroundColour),
                    new Rgb(...$this->foregroundColour),
                    new EyeFill(null, null),
                    new EyeFill(null, null),
                    new EyeFill(null, null)
                )
            ));
        }

        $writer = new Writer(new ImageRenderer(
            new RendererStyle(...$rendererStyleArgs),
            $backend
        ));

        return $writer->writeString($qrText);
    }

    /**
     * Ensure colour is an array of three values but also
     * accept a string and assume its a 3 or 6 character hex
     */
    private function handleColour($colour)
    {
        if (is_string($colour) && $colour[0] == '#') {
            $hexToRGB = function ($input) {
                // split the array into three chunks
                $split = str_split(trim($input, '#'), strlen($input) / 3);

                // cope with three character hex reference
                // three characters plus a # = 4
                if (strlen($input) == 4) {
                    array_walk($split, function (&$character) {
                        $character = str_repeat($character, 2);
                    });
                }

                // convert hex to rgb
                return array_map('hexdec', $split);
            };

            return $hexToRGB($colour);
        }

        if (is_array($colour) && count($colour) == 3) {
            return $colour;
        }

        throw new \RuntimeException('Invalid colour value');
    }
}
