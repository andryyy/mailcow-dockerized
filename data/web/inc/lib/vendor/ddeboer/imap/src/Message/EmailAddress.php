<?php

declare(strict_types=1);

namespace Ddeboer\Imap\Message;

/**
 * An e-mail address.
 */
final class EmailAddress
{
    /**
     * @var string
     */
    private $mailbox;

    /**
     * @var null|string
     */
    private $hostname;

    /**
     * @var null|string
     */
    private $name;

    /**
     * @var null|string
     */
    private $address;

    public function __construct(string $mailbox, string $hostname = null, string $name = null)
    {
        $this->mailbox  = $mailbox;
        $this->hostname = $hostname;
        $this->name     = $name;

        if (null !== $hostname) {
            $this->address = $mailbox . '@' . $hostname;
        }
    }

    /**
     * @return null|string
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Returns address with person name.
     */
    public function getFullAddress(): string
    {
        $address = \sprintf('%s@%s', $this->mailbox, $this->hostname);
        if (null !== $this->name) {
            $address = \sprintf('"%s" <%s>', \addcslashes($this->name, '"'), $address);
        }

        return $address;
    }

    public function getMailbox(): string
    {
        return $this->mailbox;
    }

    /**
     * @return null|string
     */
    public function getHostname()
    {
        return $this->hostname;
    }

    /**
     * @return null|string
     */
    public function getName()
    {
        return $this->name;
    }
}
