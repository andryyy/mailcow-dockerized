<?php
namespace RobThree\Auth\Providers\Qr;

use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\QrCode;

class EndroidQrCodeWithLogoProvider extends EndroidQrCodeProvider
{
    protected $logoPath;
    protected $logoSize;

    /**
     * Adds an image to the middle of the QR Code.
     * @param string $path Path to an image file
     * @param array|int $size Just the width, or [width, height]
     */
    public function setLogo($path, $size = null)
    {
        $this->logoPath = $path;
        $this->logoSize = (array)$size;
    }

    protected function qrCodeInstance($qrtext, $size) {
        $qrCode = parent::qrCodeInstance($qrtext, $size);

        if ($this->logoPath) {
            $qrCode->setLogoPath($this->logoPath);
            if ($this->logoSize) {
                $qrCode->setLogoSize($this->logoSize[0], $this->logoSize[1]);
            }
        }

        return $qrCode;
    }
}
