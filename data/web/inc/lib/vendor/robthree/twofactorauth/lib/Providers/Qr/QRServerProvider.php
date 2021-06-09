<?php

namespace RobThree\Auth\Providers\Qr;

// http://goqr.me/api/doc/create-qr-code/
class QRServerProvider extends BaseHTTPQRCodeProvider
{
    /** @var string */
    public $errorcorrectionlevel;

    /** @var int */
    public $margin;

    /** @var int */
    public $qzone;

    /** @var string */
    public $bgcolor;

    /** @var string */
    public $color;

    /** @var string */
    public $format;

    /**
     * @param bool $verifyssl
     * @param string $errorcorrectionlevel
     * @param int $margin
     * @param int $qzone
     * @param string $bgcolor
     * @param string $color
     * @param string $format
     */
    public function __construct($verifyssl = false, $errorcorrectionlevel = 'L', $margin = 4, $qzone = 1, $bgcolor = 'ffffff', $color = '000000', $format = 'png')
    {
        if (!is_bool($verifyssl)) {
            throw new QRException('VerifySSL must be bool');
        }

        $this->verifyssl = $verifyssl;

        $this->errorcorrectionlevel = $errorcorrectionlevel;
        $this->margin = $margin;
        $this->qzone = $qzone;
        $this->bgcolor = $bgcolor;
        $this->color = $color;
        $this->format = $format;
    }

    /**
     * {@inheritdoc}
     */
    public function getMimeType()
    {
        switch (strtolower($this->format)) {
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
        throw new QRException(sprintf('Unknown MIME-type: %s', $this->format));
    }

    /**
     * {@inheritdoc}
     */
    public function getQRCodeImage($qrtext, $size)
    {
        return $this->getContent($this->getUrl($qrtext, $size));
    }

    /**
     * @param string $value
     *
     * @return string
     */
    private function decodeColor($value)
    {
        return vsprintf('%d-%d-%d', sscanf($value, "%02x%02x%02x"));
    }

    /**
     * @param string $qrtext the value to encode in the QR code
     * @param int|string $size the desired size of the QR code
     *
     * @return string file contents of the QR code
     */
    public function getUrl($qrtext, $size)
    {
        return 'https://api.qrserver.com/v1/create-qr-code/'
            . '?size=' . $size . 'x' . $size
            . '&ecc=' . strtoupper($this->errorcorrectionlevel)
            . '&margin=' . $this->margin
            . '&qzone=' . $this->qzone
            . '&bgcolor=' . $this->decodeColor($this->bgcolor)
            . '&color=' . $this->decodeColor($this->color)
            . '&format=' . strtolower($this->format)
            . '&data=' . rawurlencode($qrtext);
    }
}
