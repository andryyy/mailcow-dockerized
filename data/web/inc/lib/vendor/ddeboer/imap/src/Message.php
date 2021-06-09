<?php

declare(strict_types=1);

namespace Ddeboer\Imap;

use Ddeboer\Imap\Exception\ImapFetchheaderException;
use Ddeboer\Imap\Exception\InvalidHeadersException;
use Ddeboer\Imap\Exception\MessageCopyException;
use Ddeboer\Imap\Exception\MessageDeleteException;
use Ddeboer\Imap\Exception\MessageDoesNotExistException;
use Ddeboer\Imap\Exception\MessageMoveException;
use Ddeboer\Imap\Exception\MessageStructureException;
use Ddeboer\Imap\Exception\MessageUndeleteException;

/**
 * An IMAP message (e-mail).
 */
final class Message extends Message\AbstractMessage implements MessageInterface
{
    private bool $messageNumberVerified = false;
    private int $imapMsgNo              = 0;
    private bool $structureLoaded       = false;
    private ?Message\Headers $headers   = null;
    private ?string $rawHeaders         = null;
    private ?string $rawMessage         = null;

    /**
     * Constructor.
     *
     * @param ImapResourceInterface $resource      IMAP resource
     * @param int                   $messageNumber Message number
     */
    public function __construct(ImapResourceInterface $resource, int $messageNumber)
    {
        parent::__construct($resource, $messageNumber, '1', new \stdClass());
    }

    protected function lazyLoadStructure(): void
    {
        if (true === $this->structureLoaded) {
            return;
        }
        $this->structureLoaded = true;

        $messageNumber = $this->getNumber();

        $errorMessage = null;
        $errorNumber  = 0;
        \set_error_handler(static function ($nr, $message) use (&$errorMessage, &$errorNumber): bool {
            $errorMessage = $message;
            $errorNumber = $nr;

            return true;
        });

        $structure = \imap_fetchstructure(
            $this->resource->getStream(),
            $messageNumber,
            \FT_UID
        );

        \restore_error_handler();

        if (!$structure instanceof \stdClass) {
            throw new MessageStructureException(\sprintf(
                'Message "%s" structure is empty: %s',
                $messageNumber,
                $errorMessage
            ), $errorNumber);
        }

        $this->setStructure($structure);
    }

    protected function assertMessageExists(int $messageNumber): void
    {
        if (true === $this->messageNumberVerified) {
            return;
        }
        $this->messageNumberVerified = true;

        $msgno = null;
        \set_error_handler(static function (): bool {
            return true;
        });

        $msgno = \imap_msgno($this->resource->getStream(), $messageNumber);

        \restore_error_handler();

        if (\is_numeric($msgno) && $msgno > 0) {
            $this->imapMsgNo = $msgno;

            return;
        }

        throw new MessageDoesNotExistException(\sprintf(
            'Message "%s" does not exist',
            $messageNumber
        ));
    }

    private function getMsgNo(): int
    {
        // Triggers assertMessageExists()
        $this->getNumber();

        return $this->imapMsgNo;
    }

    public function getRawHeaders(): string
    {
        if (null === $this->rawHeaders) {
            $rawHeaders = \imap_fetchheader($this->resource->getStream(), $this->getNumber(), \FT_UID);

            if (false === $rawHeaders) {
                throw new ImapFetchheaderException('imap_fetchheader failed');
            }

            $this->rawHeaders = $rawHeaders;
        }

        return $this->rawHeaders;
    }

    public function getRawMessage(): string
    {
        if (null === $this->rawMessage) {
            $this->rawMessage = $this->doGetContent('');
        }

        return $this->rawMessage;
    }

    public function getHeaders(): Message\Headers
    {
        if (null === $this->headers) {
            // imap_headerinfo is much faster than imap_fetchheader
            // imap_headerinfo returns only a subset of all mail headers,
            // but it does include the message flags.
            $headers = \imap_headerinfo($this->resource->getStream(), $this->getMsgNo());
            if (false === $headers) {
                // @see https://github.com/ddeboer/imap/issues/358
                throw new InvalidHeadersException(\sprintf('Message "%s" has invalid headers', $this->getNumber()));
            }
            $this->headers = new Message\Headers($headers);
        }

        return $this->headers;
    }

    /**
     * Clearmessage headers.
     */
    private function clearHeaders(): void
    {
        $this->headers = null;
    }

    public function isRecent(): ?string
    {
        $recent = $this->getHeaders()->get('recent');
        \assert(null === $recent || \is_string($recent));

        return $recent;
    }

    public function isUnseen(): bool
    {
        return 'U' === $this->getHeaders()->get('unseen');
    }

    public function isFlagged(): bool
    {
        return 'F' === $this->getHeaders()->get('flagged');
    }

    public function isAnswered(): bool
    {
        return 'A' === $this->getHeaders()->get('answered');
    }

    public function isDeleted(): bool
    {
        return 'D' === $this->getHeaders()->get('deleted');
    }

    public function isDraft(): bool
    {
        return 'X' === $this->getHeaders()->get('draft');
    }

    public function isSeen(): bool
    {
        return 'N' !== $this->getHeaders()->get('recent') && 'U' !== $this->getHeaders()->get('unseen');
    }

    public function maskAsSeen(): bool
    {
        \trigger_error(\sprintf('%s is deprecated and will be removed in 2.0. Use %s::markAsSeen instead.', __METHOD__, __CLASS__), \E_USER_DEPRECATED);

        return $this->markAsSeen();
    }

    public function markAsSeen(): bool
    {
        return $this->setFlag('\\Seen');
    }

    public function copy(MailboxInterface $mailbox): void
    {
        // 'deleted' header changed, force to reload headers, would be better to set deleted flag to true on header
        $this->clearHeaders();

        if (!\imap_mail_copy($this->resource->getStream(), (string) $this->getNumber(), $mailbox->getEncodedName(), \CP_UID)) {
            throw new MessageCopyException(\sprintf('Message "%s" cannot be copied to "%s"', $this->getNumber(), $mailbox->getName()));
        }
    }

    public function move(MailboxInterface $mailbox): void
    {
        // 'deleted' header changed, force to reload headers, would be better to set deleted flag to true on header
        $this->clearHeaders();

        if (!\imap_mail_move($this->resource->getStream(), (string) $this->getNumber(), $mailbox->getEncodedName(), \CP_UID)) {
            throw new MessageMoveException(\sprintf('Message "%s" cannot be moved to "%s"', $this->getNumber(), $mailbox->getName()));
        }
    }

    public function delete(): void
    {
        // 'deleted' header changed, force to reload headers, would be better to set deleted flag to true on header
        $this->clearHeaders();

        if (!\imap_delete($this->resource->getStream(), (string) $this->getNumber(), \FT_UID)) {
            throw new MessageDeleteException(\sprintf('Message "%s" cannot be deleted', $this->getNumber()));
        }
    }

    public function undelete(): void
    {
        // 'deleted' header changed, force to reload headers, would be better to set deleted flag to false on header
        $this->clearHeaders();
        if (!\imap_undelete($this->resource->getStream(), (string) $this->getNumber(), \FT_UID)) {
            throw new MessageUndeleteException(\sprintf('Message "%s" cannot be undeleted', $this->getNumber()));
        }
    }

    public function setFlag(string $flag): bool
    {
        $result = \imap_setflag_full($this->resource->getStream(), (string) $this->getNumber(), $flag, \ST_UID);

        $this->clearHeaders();

        return $result;
    }

    public function clearFlag(string $flag): bool
    {
        $result = \imap_clearflag_full($this->resource->getStream(), (string) $this->getNumber(), $flag, \ST_UID);

        $this->clearHeaders();

        return $result;
    }
}
