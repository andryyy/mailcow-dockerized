<?php

namespace Tests\Providers\Qr;

use RobThree\Auth\Providers\Qr\IQRCodeProvider;

class TestQrProvider implements IQRCodeProvider
{
    /**
     * {@inheritdoc}
     */
    public function getQRCodeImage($qrtext, $size)
    {
        return $qrtext . '@' . $size;
    }

    /**
     * {@inheritdoc}
     */
    public function getMimeType()
    {
        return 'test/test';
    }
}
