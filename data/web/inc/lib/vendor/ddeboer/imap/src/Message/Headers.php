<?php

declare(strict_types=1);

namespace Ddeboer\Imap\Message;

/**
 * Collection of message headers.
 */
final class Headers extends Parameters
{
    /**
     * Constructor.
     */
    public function __construct(\stdClass $headers)
    {
        parent::__construct();

        // Store all headers as lowercase
        $headers = \array_change_key_case((array) $headers);

        foreach ($headers as $key => $value) {
            $this[$key] = $this->parseHeader($key, $value);
        }
    }

    /**
     * Get header.
     *
     * @return mixed
     */
    public function get(string $key)
    {
        return parent::get(\strtolower($key));
    }

    /**
     * Parse header.
     *
     * @param mixed $value
     *
     * @return mixed
     */
    private function parseHeader(string $key, $value)
    {
        switch ($key) {
            case 'msgno':
                return (int) $value;
            case 'from':
            case 'to':
            case 'cc':
            case 'bcc':
            case 'reply_to':
            case 'sender':
            case 'return_path':
                /** @var \stdClass $address */
                foreach ($value as $address) {
                    if (isset($address->mailbox)) {
                        $address->host     = $address->host ?? null;
                        $address->personal = isset($address->personal) ? $this->decode($address->personal) : null;
                    }
                }

                return $value;
            case 'date':
            case 'subject':
                return $this->decode($value);
        }

        return $value;
    }
}
