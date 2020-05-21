<?php

declare(strict_types=1);

namespace Ddeboer\Imap;

use Ddeboer\Imap\Exception\AuthenticationFailedException;
use Ddeboer\Imap\Exception\ResourceCheckFailureException;

/**
 * An IMAP server.
 */
final class Server implements ServerInterface
{
    /**
     * @var string Internet domain name or bracketed IP address of server
     */
    private $hostname;

    /**
     * @var string TCP port number
     */
    private $port;

    /**
     * @var string Optional flags
     */
    private $flags;

    /**
     * @var array
     */
    private $parameters;

    /**
     * @var int Connection options
     */
    private $options;

    /**
     * @var int Retries number
     */
    private $retries;

    /**
     * Constructor.
     *
     * @param string $hostname   Internet domain name or bracketed IP address
     *                           of server
     * @param string $port       TCP port number
     * @param string $flags      Optional flags
     * @param array  $parameters Connection parameters
     * @param int    $options    Connection options
     * @param int    $retries    Retries number
     */
    public function __construct(
        string $hostname,
        string $port = '993',
        string $flags = '/imap/ssl/validate-cert',
        array $parameters = [],
        int $options = 0,
        int $retries = 1
    ) {
        if (!\function_exists('imap_open')) {
            throw new \RuntimeException('IMAP extension must be enabled');
        }

        $this->hostname   = $hostname;
        $this->port       = $port;
        $this->flags      = '' !== $flags ? '/' . \ltrim($flags, '/') : '';
        $this->parameters = $parameters;
        $this->options    = $options;
        $this->retries    = $retries;
    }

    /**
     * Authenticate connection.
     *
     * @param string $username Username
     * @param string $password Password
     *
     * @throws AuthenticationFailedException
     */
    public function authenticate(string $username, string $password): ConnectionInterface
    {
        $errorMessage = null;
        $errorNumber  = 0;
        \set_error_handler(static function ($nr, $message) use (&$errorMessage, &$errorNumber): bool {
            $errorMessage = $message;
            $errorNumber = $nr;

            return true;
        });

        $resource = \imap_open(
            $this->getServerString(),
            $username,
            $password,
            $this->options,
            $this->retries,
            $this->parameters
        );

        \restore_error_handler();

        if (false === $resource || null !== $errorMessage) {
            throw new AuthenticationFailedException(\sprintf(
                'Authentication failed for user "%s"%s',
                $username,
                null !== $errorMessage ? ': ' . $errorMessage : ''
            ), $errorNumber);
        }

        $check = \imap_check($resource);

        if (false === $check) {
            throw new ResourceCheckFailureException('Resource check failure');
        }

        $mailbox       = $check->Mailbox;
        $connection    = $mailbox;
        $curlyPosition = \strpos($mailbox, '}');
        if (false !== $curlyPosition) {
            $connection = \substr($mailbox, 0, $curlyPosition + 1);
        }

        // These are necessary to get rid of PHP throwing IMAP errors
        \imap_errors();
        \imap_alerts();

        return new Connection(new ImapResource($resource), $connection);
    }

    /**
     * Glues hostname, port and flags and returns result.
     */
    private function getServerString(): string
    {
        return \sprintf(
            '{%s%s%s}',
            $this->hostname,
            '' !== $this->port ? ':' . $this->port : '',
            $this->flags
        );
    }
}
