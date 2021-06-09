<?php

declare(strict_types=1);

namespace Ddeboer\Imap;

use Ddeboer\Imap\Exception\MessageCopyException;
use Ddeboer\Imap\Exception\MessageDeleteException;
use Ddeboer\Imap\Exception\MessageMoveException;
use Ddeboer\Imap\Exception\MessageUndeleteException;

/**
 * An IMAP message (e-mail).
 */
interface MessageInterface extends Message\BasicMessageInterface
{
    /**
     * Get raw part content.
     */
    public function getContent(): string;

    /**
     * Get message recent flag value (from headers).
     */
    public function isRecent(): ?string;

    /**
     * Get message unseen flag value (from headers).
     */
    public function isUnseen(): bool;

    /**
     * Get message flagged flag value (from headers).
     */
    public function isFlagged(): bool;

    /**
     * Get message answered flag value (from headers).
     */
    public function isAnswered(): bool;

    /**
     * Get message deleted flag value (from headers).
     */
    public function isDeleted(): bool;

    /**
     * Get message draft flag value (from headers).
     */
    public function isDraft(): bool;

    /**
     * Has the message been marked as read?
     */
    public function isSeen(): bool;

    /**
     * Mark message as seen.
     *
     * @deprecated since version 1.1, to be removed in 2.0
     */
    public function maskAsSeen(): bool;

    /**
     * Mark message as seen.
     */
    public function markAsSeen(): bool;

    /**
     * Move message to another mailbox.
     *
     * @throws MessageCopyException
     */
    public function copy(MailboxInterface $mailbox): void;

    /**
     * Move message to another mailbox.
     *
     * @throws MessageMoveException
     */
    public function move(MailboxInterface $mailbox): void;

    /**
     * Delete message.
     *
     * @throws MessageDeleteException
     */
    public function delete(): void;

    /**
     * Undelete message.
     *
     * @throws MessageUndeleteException
     */
    public function undelete(): void;

    /**
     * Set Flag Message.
     *
     * @param string $flag \Seen, \Answered, \Flagged, \Deleted, and \Draft
     */
    public function setFlag(string $flag): bool;

    /**
     * Clear Flag Message.
     *
     * @param string $flag \Seen, \Answered, \Flagged, \Deleted, and \Draft
     */
    public function clearFlag(string $flag): bool;
}
