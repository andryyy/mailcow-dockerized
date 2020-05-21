<?php

declare(strict_types=1);

namespace Ddeboer\Imap\Message;

class Parameters extends \ArrayIterator
{
    /**
     * @var array
     */
    private static $attachmentCustomKeys = [
        'name*'     => 'name',
        'filename*' => 'filename',
    ];

    public function __construct(array $parameters = [])
    {
        parent::__construct();

        $this->add($parameters);
    }

    public function add(array $parameters = []): void
    {
        foreach ($parameters as $parameter) {
            $key = \strtolower($parameter->attribute);
            if (isset(self::$attachmentCustomKeys[$key])) {
                $key = self::$attachmentCustomKeys[$key];
            }
            $value      = $this->decode($parameter->value);
            $this[$key] = $value;
        }
    }

    /**
     * @return mixed
     */
    public function get(string $key)
    {
        return $this[$key] ?? null;
    }

    /**
     * Decode value.
     */
    final protected function decode(string $value): string
    {
        $parts = \imap_mime_header_decode($value);
        if (!\is_array($parts)) {
            return $value;
        }

        $decoded = '';
        foreach ($parts as $part) {
            $text = $part->text;
            if ('default' !== $part->charset) {
                $text = Transcoder::decode($text, $part->charset);
            }
            // RFC2231
            if (1 === \preg_match('/^(?<encoding>[^\']+)\'[^\']*?\'(?<urltext>.+)$/', $text, $matches)) {
                $hasInvalidChars = 1 === \preg_match('#[^%a-zA-Z0-9\-_\.\+]#', $matches['urltext']);
                $hasEscapedChars = 1 === \preg_match('#%[a-zA-Z0-9]{2}#', $matches['urltext']);
                if (!$hasInvalidChars && $hasEscapedChars) {
                    $text = Transcoder::decode(\urldecode($matches['urltext']), $matches['encoding']);
                }
            }

            $decoded .= $text;
        }

        return $decoded;
    }
}
