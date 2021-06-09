<?php

declare(strict_types=1);

namespace Ddeboer\Imap\Message;

final class Headers extends Parameters
{
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
     * @return null|int|\stdClass[]|string
     */
    public function get(string $key)
    {
        return parent::get(\strtolower($key));
    }

    /**
     * Parse header.
     *
     * @param int|\stdClass[]|string $value
     *
     * @return int|\stdClass[]|string
     */
    private function parseHeader(string $key, $value)
    {
        switch ($key) {
            case 'msgno':
                \assert(\is_string($value));

                return (int) $value;
            case 'from':
            case 'to':
            case 'cc':
            case 'bcc':
            case 'reply_to':
            case 'sender':
            case 'return_path':
                \assert(\is_array($value));
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
                \assert(\is_string($value));

                return $this->decode($value);
        }

        return $value;
    }
}
