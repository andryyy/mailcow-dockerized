<?php

declare(strict_types=1);

namespace Ddeboer\Imap\Message;

/**
 * @extends \ArrayIterator<int|string, int|string|\stdClass[]>
 */
class Parameters extends \ArrayIterator
{
    /**
     * @var array<string, string>
     */
    private static array $attachmentCustomKeys = [
        'name*'     => 'name',
        'filename*' => 'filename',
    ];

    /**
     * @param \stdClass[] $parameters
     */
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
     * @return null|int|\stdClass[]|string
     */
    public function get(string $key)
    {
        return $this[$key] ?? null;
    }

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
