<?php
namespace RobThree\Auth\Providers\Qr;

use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\QrCode;

class EndroidQrCodeProvider implements IQRCodeProvider
{
    public $bgcolor;
    public $color;
    public $margin;
    public $errorcorrectionlevel;

    public function __construct($bgcolor = 'ffffff', $color = '000000', $margin = 0, $errorcorrectionlevel = 'H')
    {
        $this->bgcolor = $this->handleColor($bgcolor);
        $this->color = $this->handleColor($color);
        $this->margin = $margin;
        $this->errorcorrectionlevel = $this->handleErrorCorrectionLevel($errorcorrectionlevel);
    }

    public function getMimeType()
    {
        return 'image/png';
    }

    public function getQRCodeImage($qrtext, $size)
    {
        return $this->qrCodeInstance($qrtext, $size)->writeString();
    }

    protected function qrCodeInstance($qrtext, $size)
    {
        $qrCode = new QrCode($qrtext);
        $qrCode->setSize($size);

        $qrCode->setErrorCorrectionLevel($this->errorcorrectionlevel);
        $qrCode->setMargin($this->margin);
        $qrCode->setBackgroundColor($this->bgcolor);
        $qrCode->setForegroundColor($this->color);

        return $qrCode;
    }

    private function handleColor($color)
    {
        $split = str_split($color, 2);
        $r = hexdec($split[0]);
        $g = hexdec($split[1]);
        $b = hexdec($split[2]);

        return ['r' => $r, 'g' => $g, 'b' => $b, 'a' => 0];
    }

    private function handleErrorCorrectionLevel($level)
    {
        switch ($level) {
            case 'L':
                return ErrorCorrectionLevel::LOW();
            case 'M':
                return ErrorCorrectionLevel::MEDIUM();
            case 'Q':
                return ErrorCorrectionLevel::QUARTILE();
            case 'H':
                return ErrorCorrectionLevel::HIGH();
            default:
                return ErrorCorrectionLevel::HIGH();
        }
    }
}
