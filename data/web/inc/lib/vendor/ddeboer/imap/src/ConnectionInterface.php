<?php

declare(strict_types=1);

namespace Ddeboer\Imap;

/**
 * A connection to an IMAP server that is authenticated for a user.
 */
interface ConnectionInterface extends \Countable
{
    /**
     * Get IMAP resource.
     */
    public function getResource(): ImapResourceInterface;

    /**
     * Delete all messages marked for deletion.
     */
    public function expunge(): bool;

    /**
     * Close connection.
     */
    public function close(int $flag = 0): bool;

    /**
     * Check if the connection is still active.
     */
    public function ping(): bool;

    /**
     * Get Mailbox quota.
     */
    public function getQuota(string $root = 'INBOX'): array;

    /**
     * Get a list of mailboxes (also known as folders).
     *
     * @return MailboxInterface[]
     */
    public function getMailboxes(): array;

    /**
     * Check that a mailbox with the given name exists.
     *
     * @param string $name Mailbox name
     */
    public function hasMailbox(string $name): bool;

    /**
     * Get a mailbox by its name.
     *
     * @param string $name Mailbox name
     */
    public function getMailbox(string $name): MailboxInterface;

    /**
     * Create mailbox.
     */
    public function createMailbox(string $name): MailboxInterface;

    /**
     * Delete mailbox.
     */
    public function deleteMailbox(MailboxInterface $mailbox): void;
}
