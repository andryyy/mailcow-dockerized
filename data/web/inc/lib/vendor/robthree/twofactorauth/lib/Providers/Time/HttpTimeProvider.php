<?php

namespace RobThree\Auth\Providers\Time;

use DateTime;

/**
 * Takes the time from any webserver by doing a HEAD request on the specified URL and extracting the 'Date:' header
 */
class HttpTimeProvider implements ITimeProvider
{
    /** @var string */
    public $url;

    /** @var string */
    public $expectedtimeformat;

    /** @var array */
    public $options;

    /**
     * @param string $url
     * @param string $expectedtimeformat
     * @param array $options
     */
    public function __construct($url = 'https://google.com', $expectedtimeformat = 'D, d M Y H:i:s O+', array $options = null)
    {
        $this->url = $url;
        $this->expectedtimeformat = $expectedtimeformat;
        if ($options === null) {
            $options = array(
                'http' => array(
                    'method' => 'HEAD',
                    'follow_location' => false,
                    'ignore_errors' => true,
                    'max_redirects' => 0,
                    'request_fulluri' => true,
                    'header' => array(
                        'Connection: close',
                        'User-agent: TwoFactorAuth HttpTimeProvider (https://github.com/RobThree/TwoFactorAuth)',
                        'Cache-Control: no-cache'
                    )
                )
            );
        }
        $this->options = $options;
    }

    /**
     * {@inheritdoc}
     */
    public function getTime()
    {
        try {
            $context  = stream_context_create($this->options);
            $fd = fopen($this->url, 'rb', false, $context);
            $headers = stream_get_meta_data($fd);
            fclose($fd);

            foreach ($headers['wrapper_data'] as $h) {
                if (strcasecmp(substr($h, 0, 5), 'Date:') === 0) {
                    return DateTime::createFromFormat($this->expectedtimeformat, trim(substr($h, 5)))->getTimestamp();
                }
            }
            throw new \Exception('Invalid or no "Date:" header found');
        } catch (\Exception $ex) {
            throw new TimeException(sprintf('Unable to retrieve time from %s (%s)', $this->url, $ex->getMessage()));
        }

    }
}
