<?php

declare(strict_types=1);

namespace Ddeboer\Imap\Message;

use Ddeboer\Imap\Exception\ImapFetchbodyException;
use Ddeboer\Imap\Exception\UnexpectedEncodingException;
use Ddeboer\Imap\ImapResourceInterface;
use Ddeboer\Imap\Message;

/**
 * A message part.
 */
abstract class AbstractPart implements PartInterface
{
    /**
     * @var ImapResourceInterface
     */
    protected $resource;

    /**
     * @var bool
     */
    private $structureParsed = false;

    /**
     * @var array
     */
    private $parts = [];

    /**
     * @var string
     */
    private $partNumber;

    /**
     * @var int
     */
    private $messageNumber;

    /**
     * @var \stdClass
     */
    private $structure;

    /**
     * @var Parameters
     */
    private $parameters;

    /**
     * @var null|string
     */
    private $type;

    /**
     * @var null|string
     */
    private $subtype;

    /**
     * @var null|string
     */
    private $encoding;

    /**
     * @var null|string
     */
    private $disposition;

    /**
     * @var null|string
     */
    private $description;

    /**
     * @var null|string
     */
    private $bytes;

    /**
     * @var null|string
     */
    private $lines;

    /**
     * @var null|string
     */
    private $content;

    /**
     * @var null|string
     */
    private $decodedContent;

    /**
     * @var int
     */
    private $key = 0;

    /**
     * @var array
     */
    private static $typesMap = [
        \TYPETEXT        => self::TYPE_TEXT,
        \TYPEMULTIPART   => self::TYPE_MULTIPART,
        \TYPEMESSAGE     => self::TYPE_MESSAGE,
        \TYPEAPPLICATION => self::TYPE_APPLICATION,
        \TYPEAUDIO       => self::TYPE_AUDIO,
        \TYPEIMAGE       => self::TYPE_IMAGE,
        \TYPEVIDEO       => self::TYPE_VIDEO,
        \TYPEMODEL       => self::TYPE_MODEL,
        \TYPEOTHER       => self::TYPE_OTHER,
    ];

    /**
     * @var array
     */
    private static $encodingsMap = [
        \ENC7BIT            => self::ENCODING_7BIT,
        \ENC8BIT            => self::ENCODING_8BIT,
        \ENCBINARY          => self::ENCODING_BINARY,
        \ENCBASE64          => self::ENCODING_BASE64,
        \ENCQUOTEDPRINTABLE => self::ENCODING_QUOTED_PRINTABLE,
    ];

    /**
     * @var array
     */
    private static $attachmentKeys = [
        'name'      => true,
        'filename'  => true,
        'name*'     => true,
        'filename*' => true,
    ];

    /**
     * Constructor.
     *
     * @param ImapResourceInterface $resource      IMAP resource
     * @param int                   $messageNumber Message number
     * @param string                $partNumber    Part number
     * @param \stdClass             $structure     Part structure
     */
    public function __construct(
        ImapResourceInterface $resource,
        int $messageNumber,
        string $partNumber,
        \stdClass $structure
    ) {
        $this->resource      = $resource;
        $this->messageNumber = $messageNumber;
        $this->partNumber    = $partNumber;
        $this->setStructure($structure);
    }

    /**
     * Get message number (from headers).
     */
    final public function getNumber(): int
    {
        $this->assertMessageExists($this->messageNumber);

        return $this->messageNumber;
    }

    /**
     * Ensure message exists.
     */
    protected function assertMessageExists(int $messageNumber): void
    {
    }

    /**
     * @param \stdClass $structure Part structure
     */
    final protected function setStructure(\stdClass $structure): void
    {
        $this->structure = $structure;
    }

    /**
     * Part structure.
     */
    final public function getStructure(): \stdClass
    {
        $this->lazyLoadStructure();

        return $this->structure;
    }

    /**
     * Lazy load structure.
     */
    protected function lazyLoadStructure(): void
    {
    }

    /**
     * Part parameters.
     */
    final public function getParameters(): Parameters
    {
        $this->lazyParseStructure();

        return $this->parameters;
    }

    /**
     * Part charset.
     */
    final public function getCharset(): ?string
    {
        $this->lazyParseStructure();

        return $this->parameters->get('charset') ?: null;
    }

    /**
     * Part type.
     */
    final public function getType(): ?string
    {
        $this->lazyParseStructure();

        return $this->type;
    }

    /**
     * Part subtype.
     */
    final public function getSubtype(): ?string
    {
        $this->lazyParseStructure();

        return $this->subtype;
    }

    /**
     * Part encoding.
     */
    final public function getEncoding(): ?string
    {
        $this->lazyParseStructure();

        return $this->encoding;
    }

    /**
     * Part disposition.
     */
    final public function getDisposition(): ?string
    {
        $this->lazyParseStructure();

        return $this->disposition;
    }

    /**
     * Part description.
     */
    final public function getDescription(): ?string
    {
        $this->lazyParseStructure();

        return $this->description;
    }

    /**
     * Part bytes.
     *
     * @return null|int|string
     */
    final public function getBytes()
    {
        $this->lazyParseStructure();

        return $this->bytes;
    }

    /**
     * Part lines.
     */
    final public function getLines(): ?string
    {
        $this->lazyParseStructure();

        return $this->lines;
    }

    /**
     * Get raw part content.
     */
    final public function getContent(): string
    {
        if (null === $this->content) {
            $this->content = $this->doGetContent($this->getContentPartNumber());
        }

        return $this->content;
    }

    /**
     * Get content part number.
     */
    protected function getContentPartNumber(): string
    {
        return $this->partNumber;
    }

    /**
     * Get part number.
     */
    final public function getPartNumber(): string
    {
        return $this->partNumber;
    }

    /**
     * Get decoded part content.
     */
    final public function getDecodedContent(): string
    {
        if (null === $this->decodedContent) {
            if (self::ENCODING_UNKNOWN === $this->getEncoding()) {
                throw new UnexpectedEncodingException('Cannot decode a content with an uknown encoding');
            }

            $content = $this->getContent();
            if (self::ENCODING_BASE64 === $this->getEncoding()) {
                $content = \base64_decode($content, false);
            } elseif (self::ENCODING_QUOTED_PRINTABLE === $this->getEncoding()) {
                $content = \quoted_printable_decode($content);
            }

            if (false === $content) {
                throw new UnexpectedEncodingException('Cannot decode content');
            }

            // If this part is a text part, convert its charset to UTF-8.
            // We don't want to decode an attachment's charset.
            if (!$this instanceof Attachment && null !== $this->getCharset() && self::TYPE_TEXT === $this->getType()) {
                $content = Transcoder::decode($content, $this->getCharset());
            }

            $this->decodedContent = $content;
        }

        return $this->decodedContent;
    }

    /**
     * Get raw message content.
     */
    final protected function doGetContent(string $partNumber): string
    {
        $return = \imap_fetchbody(
            $this->resource->getStream(),
            $this->getNumber(),
            $partNumber,
            \FT_UID | \FT_PEEK
        );

        if (false === $return) {
            throw new ImapFetchbodyException('imap_fetchbody failed');
        }

        return $return;
    }

    /**
     * Get an array of all parts for this message.
     *
     * @return PartInterface[]
     */
    final public function getParts(): array
    {
        $this->lazyParseStructure();

        return $this->parts;
    }

    /**
     * Get current child part.
     *
     * @return mixed
     */
    final public function current()
    {
        $this->lazyParseStructure();

        return $this->parts[$this->key];
    }

    /**
     * Get current child part.
     *
     * @return \RecursiveIterator
     */
    final public function getChildren()
    {
        return $this->current();
    }

    /**
     * Get current child part.
     *
     * @return bool
     */
    final public function hasChildren()
    {
        $this->lazyParseStructure();

        return \count($this->parts) > 0;
    }

    /**
     * Get current part key.
     *
     * @return int
     */
    final public function key()
    {
        return $this->key;
    }

    /**
     * Move to next part.
     *
     * @return void
     */
    final public function next()
    {
        ++$this->key;
    }

    /**
     * Reset part key.
     *
     * @return void
     */
    final public function rewind()
    {
        $this->key = 0;
    }

    /**
     * Check if current part is a valid one.
     *
     * @return bool
     */
    final public function valid()
    {
        $this->lazyParseStructure();

        return isset($this->parts[$this->key]);
    }

    /**
     * Parse part structure.
     */
    private function lazyParseStructure(): void
    {
        if (true === $this->structureParsed) {
            return;
        }
        $this->structureParsed = true;

        $this->lazyLoadStructure();

        $this->type = self::$typesMap[$this->structure->type] ?? self::TYPE_UNKNOWN;

        // In our context, \ENCOTHER is as useful as an unknown encoding
        $this->encoding = self::$encodingsMap[$this->structure->encoding] ?? self::ENCODING_UNKNOWN;
        if (isset($this->structure->subtype)) {
            $this->subtype = $this->structure->subtype;
        }

        if (isset($this->structure->bytes)) {
            $this->bytes = $this->structure->bytes;
        }
        if ($this->structure->ifdisposition) {
            $this->disposition = $this->structure->disposition;
        }
        if ($this->structure->ifdescription) {
            $this->description = $this->structure->description;
        }

        $this->parameters = new Parameters();
        if ($this->structure->ifparameters) {
            $this->parameters->add($this->structure->parameters);
        }

        if ($this->structure->ifdparameters) {
            $this->parameters->add($this->structure->dparameters);
        }

        // When the message is not multipart and the body is the attachment content
        // Prevents infinite recursion
        if (self::isAttachment($this->structure) && !$this instanceof Attachment) {
            $this->parts[] = new Attachment($this->resource, $this->getNumber(), '1', $this->structure);
        }

        if (isset($this->structure->parts)) {
            $parts = $this->structure->parts;
            // https://secure.php.net/manual/en/function.imap-fetchbody.php#89002
            if ($this instanceof Attachment && $this->isEmbeddedMessage() && 1 === \count($parts) && \TYPEMULTIPART === $parts[0]->type) {
                $parts = $parts[0]->parts;
            }
            foreach ($parts as $key => $partStructure) {
                $partNumber = (!$this instanceof Message) ? $this->partNumber . '.' : '';
                $partNumber .= (string) ($key + 1);

                $newPartClass = self::isAttachment($partStructure)
                    ? Attachment::class
                    : SimplePart::class
                ;

                $this->parts[] = new $newPartClass($this->resource, $this->getNumber(), $partNumber, $partStructure);
            }
        }
    }

    /**
     * Check if the given part is an attachment.
     */
    private static function isAttachment(\stdClass $part): bool
    {
        if (isset(self::$typesMap[$part->type]) && self::TYPE_MULTIPART === self::$typesMap[$part->type]) {
            return false;
        }

        // Attachment with correct Content-Disposition header
        if ($part->ifdisposition) {
            if ('attachment' === \strtolower($part->disposition)) {
                return true;
            }

            if (
                    'inline' === \strtolower($part->disposition)
                && self::SUBTYPE_PLAIN !== \strtoupper($part->subtype)
                && self::SUBTYPE_HTML !== \strtoupper($part->subtype)
            ) {
                return true;
            }
        }

        // Attachment without Content-Disposition header
        if ($part->ifparameters) {
            foreach ($part->parameters as $parameter) {
                if (isset(self::$attachmentKeys[\strtolower($parameter->attribute)])) {
                    return true;
                }
            }
        }

        /*
        if ($part->ifdparameters) {
            foreach ($part->dparameters as $parameter) {
                if (isset(self::$attachmentKeys[\strtolower($parameter->attribute)])) {
                    return true;
                }
            }
        }
         */

        if (self::SUBTYPE_RFC822 === \strtoupper($part->subtype)) {
            return true;
        }

        return false;
    }
}
