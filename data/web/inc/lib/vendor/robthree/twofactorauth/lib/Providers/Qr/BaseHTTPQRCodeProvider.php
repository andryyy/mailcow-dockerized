<?php

namespace RobThree\Auth\Providers\Qr;

abstract class BaseHTTPQRCodeProvider implements IQRCodeProvider
{
    /** @var bool */
    protected $verifyssl;

    /**
     * @param string $url
     *
     * @return string|bool
     */
    protected function getContent($url)
    {
        $curlhandle = curl_init();

        curl_setopt_array($curlhandle, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_DNS_CACHE_TIMEOUT => 10,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => $this->verifyssl,
            CURLOPT_USERAGENT => 'TwoFactorAuth'
        ));
        $data = curl_exec($curlhandle);

        curl_close($curlhandle);
        return $data;
    }
}
