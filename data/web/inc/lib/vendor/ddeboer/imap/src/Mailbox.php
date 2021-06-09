<?php

declare(strict_types=1);

namespace Ddeboer\Imap;

use DateTimeInterface;
use Ddeboer\Imap\Exception\ImapNumMsgException;
use Ddeboer\Imap\Exception\ImapStatusException;
use Ddeboer\Imap\Exception\InvalidSearchCriteriaException;
use Ddeboer\Imap\Exception\MessageCopyException;
use Ddeboer\Imap\Exception\MessageMoveException;
use Ddeboer\Imap\Search\ConditionInterface;
use Ddeboer\Imap\Search\LogicalOperator\All;

/**
 * An IMAP mailbox (commonly referred to as a 'folder').
 */
final class Mailbox implements MailboxInterface
{
    private ImapResourceInterface $resource;
    private string $name;
    private \stdClass $info;

    /**
     * Constructor.
     *
     * @param ImapResourceInterface $resource IMAP resource
     * @param string                $name     Mailbox decoded name
     * @param \stdClass             $info     Mailbox info
     */
    public function __construct(ImapResourceInterface $resource, string $name, \stdClass $info)
    {
        $this->resource = new ImapResource($resource->getStream(), $this);
        $this->name     = $name;
        $this->info     = $info;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getEncodedName(): string
    {
        /** @var string $name */
        $name = $this->info->name;

        return (string) \preg_replace('/^{.+}/', '', $name);
    }

    public function getFullEncodedName(): string
    {
        return $this->info->name;
    }

    public function getAttributes(): int
    {
        return $this->info->attributes;
    }

    public function getDelimiter(): string
    {
        return $this->info->delimiter;
    }

    public function count()
    {
        $return = \imap_num_msg($this->resource->getStream());

        if (false === $return) {
            throw new ImapNumMsgException('imap_num_msg failed');
        }

        return $return;
    }

    public function getStatus(int $flags = null): \stdClass
    {
        $return = \imap_status($this->resource->getStream(), $this->getFullEncodedName(), $flags ?? \SA_ALL);

        if (false === $return) {
            throw new ImapStatusException('imap_status failed');
        }

        return $return;
    }

    public function setFlag(string $flag, $numbers): bool
    {
        return \imap_setflag_full($this->resource->getStream(), $this->prepareMessageIds($numbers), $flag, \ST_UID);
    }

    public function clearFlag(string $flag, $numbers): bool
    {
        return \imap_clearflag_full($this->resource->getStream(), $this->prepareMessageIds($numbers), $flag, \ST_UID);
    }

    public function getMessages(ConditionInterface $search = null, int $sortCriteria = null, bool $descending = false, string $charset = null): MessageIteratorInterface
    {
        if (null === $search) {
            $search = new All();
        }
        $query = $search->toString();

        if (\PHP_VERSION_ID < 80000) {
            $descending = (int) $descending;
        }

        // We need to clear the stack to know whether imap_last_error()
        // is related to this imap_search
        \imap_errors();

        if (null !== $sortCriteria) {
            $params = [
                $this->resource->getStream(),
                $sortCriteria,
                $descending,
                \SE_UID,
                $query,
            ];
            if (null !== $charset) {
                $params[] = $charset;
            }
            $messageNumbers = \imap_sort(...$params);
        } else {
            $params = [
                $this->resource->getStream(),
                $query,
                \SE_UID,
            ];
            if (null !== $charset) {
                $params[] = $charset;
            }
            $messageNumbers = \imap_search(...$params);
        }
        if (false !== \imap_last_error()) {
            // this way all errors occurred during search will be reported
            throw new InvalidSearchCriteriaException(
                \sprintf('Invalid search criteria [%s]', $query)
            );
        }
        if (false === $messageNumbers) {
            // imap_search can also return false
            $messageNumbers = [];
        }

        return new MessageIterator($this->resource, $messageNumbers);
    }

    public function getMessageSequence(string $sequence): MessageIteratorInterface
    {
        \imap_errors();

        $overview = \imap_fetch_overview($this->resource->getStream(), $sequence, \FT_UID);
        if (false !== \imap_last_error()) {
            throw new InvalidSearchCriteriaException(
                \sprintf('Invalid sequence [%s]', $sequence)
            );
        }
        if (\is_array($overview) && [] !== $overview) {
            $messageNumbers = \array_column($overview, 'uid');
        } else {
            $messageNumbers = [];
        }

        return new MessageIterator($this->resource, $messageNumbers);
    }

    public function getMessage(int $number): MessageInterface
    {
        return new Message($this->resource, $number);
    }

    public function getIterator(): MessageIteratorInterface
    {
        return $this->getMessages();
    }

    public function addMessage(string $message, string $options = null, DateTimeInterface $internalDate = null): bool
    {
        $arguments = [
            $this->resource->getStream(),
            $this->getFullEncodedName(),
            $message,
            $options ?? '',
        ];
        if (null !== $internalDate) {
            $arguments[] = $internalDate->format('d-M-Y H:i:s O');
        }

        return \imap_append(...$arguments);
    }

    public function getThread(): array
    {
        \set_error_handler(static function (): bool {
            return true;
        });

        /** @var array<string, int>|false $tree */
        $tree = \imap_thread($this->resource->getStream(), \SE_UID);

        \restore_error_handler();

        return false !== $tree ? $tree : [];
    }

    public function move($numbers, MailboxInterface $mailbox): void
    {
        if (!\imap_mail_copy($this->resource->getStream(), $this->prepareMessageIds($numbers), $mailbox->getEncodedName(), \CP_UID | \CP_MOVE)) {
            throw new MessageMoveException(\sprintf('Messages cannot be moved to "%s"', $mailbox->getName()));
        }
    }

    public function copy($numbers, MailboxInterface $mailbox): void
    {
        if (!\imap_mail_copy($this->resource->getStream(), $this->prepareMessageIds($numbers), $mailbox->getEncodedName(), \CP_UID)) {
            throw new MessageCopyException(\sprintf('Messages cannot be copied to "%s"', $mailbox->getName()));
        }
    }

    /**
     * Prepare message ids for the use with bulk functions.
     *
     * @param array<int, int|string>|MessageIterator|string $messageIds Message numbers
     */
    private function prepareMessageIds($messageIds): string
    {
        if ($messageIds instanceof MessageIterator) {
            $messageIds = $messageIds->getArrayCopy();
        }

        if (\is_array($messageIds)) {
            $messageIds = \implode(',', $messageIds);
        }

        return $messageIds;
    }
}
