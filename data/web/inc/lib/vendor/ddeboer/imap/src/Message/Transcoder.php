<?php

declare(strict_types=1);

namespace Ddeboer\Imap\Message;

use Ddeboer\Imap\Exception\UnsupportedCharsetException;

final class Transcoder
{
    /**
     * @var array
     *
     * @see https://encoding.spec.whatwg.org/#encodings
     * @see https://dxr.mozilla.org/mozilla-central/source/dom/encoding/labelsencodings.properties
     * @see https://dxr.mozilla.org/mozilla1.9.1/source/intl/uconv/src/charsetalias.properties
     * @see https://msdn.microsoft.com/en-us/library/cc194829.aspx
     */
    private static $charsetAliases = [
        '128'                       => 'Shift_JIS',
        '129'                       => 'EUC-KR',
        '134'                       => 'GB2312',
        '136'                       => 'Big5',
        '161'                       => 'windows-1253',
        '162'                       => 'windows-1254',
        '177'                       => 'windows-1255',
        '178'                       => 'windows-1256',
        '186'                       => 'windows-1257',
        '204'                       => 'windows-1251',
        '222'                       => 'windows-874',
        '238'                       => 'windows-1250',
        '5601'                      => 'EUC-KR',
        '646'                       => 'us-ascii',
        '850'                       => 'IBM850',
        '852'                       => 'IBM852',
        '855'                       => 'IBM855',
        '857'                       => 'IBM857',
        '862'                       => 'IBM862',
        '864'                       => 'IBM864',
        '864i'                      => 'IBM864i',
        '866'                       => 'IBM866',
        'ansi-1251'                 => 'windows-1251',
        'ansi_x3.4-1968'            => 'us-ascii',
        'arabic'                    => 'ISO-8859-6',
        'ascii'                     => 'us-ascii',
        'asmo-708'                  => 'ISO-8859-6',
        'big5-hkscs'                => 'Big5',
        'chinese'                   => 'GB2312',
        'cn-big5'                   => 'Big5',
        'cns11643'                  => 'x-euc-tw',
        'cp-866'                    => 'IBM866',
        'cp1250'                    => 'windows-1250',
        'cp1251'                    => 'windows-1251',
        'cp1252'                    => 'windows-1252',
        'cp1253'                    => 'windows-1253',
        'cp1254'                    => 'windows-1254',
        'cp1255'                    => 'windows-1255',
        'cp1256'                    => 'windows-1256',
        'cp1257'                    => 'windows-1257',
        'cp1258'                    => 'windows-1258',
        'cp819'                     => 'ISO-8859-1',
        'cp850'                     => 'IBM850',
        'cp852'                     => 'IBM852',
        'cp855'                     => 'IBM855',
        'cp857'                     => 'IBM857',
        'cp862'                     => 'IBM862',
        'cp864'                     => 'IBM864',
        'cp864i'                    => 'IBM864i',
        'cp866'                     => 'IBM866',
        'cp932'                     => 'Shift_JIS',
        'csbig5'                    => 'Big5',
        'cseucjpkdfmtjapanese'      => 'EUC-JP',
        'cseuckr'                   => 'EUC-KR',
        'cseucpkdfmtjapanese'       => 'EUC-JP',
        'csgb2312'                  => 'GB2312',
        'csibm850'                  => 'IBM850',
        'csibm852'                  => 'IBM852',
        'csibm855'                  => 'IBM855',
        'csibm857'                  => 'IBM857',
        'csibm862'                  => 'IBM862',
        'csibm864'                  => 'IBM864',
        'csibm864i'                 => 'IBM864i',
        'csibm866'                  => 'IBM866',
        'csiso103t618bit'           => 'T.61-8bit',
        'csiso111ecmacyrillic'      => 'ISO-IR-111',
        'csiso2022jp'               => 'ISO-2022-JP',
        'csiso2022jp2'              => 'ISO-2022-JP',
        'csiso2022kr'               => 'ISO-2022-KR',
        'csiso58gb231280'           => 'GB2312',
        'csiso88596e'               => 'ISO-8859-6-E',
        'csiso88596i'               => 'ISO-8859-6-I',
        'csiso88598e'               => 'ISO-8859-8-E',
        'csiso88598i'               => 'ISO-8859-8-I',
        'csisolatin1'               => 'ISO-8859-1',
        'csisolatin2'               => 'ISO-8859-2',
        'csisolatin3'               => 'ISO-8859-3',
        'csisolatin4'               => 'ISO-8859-4',
        'csisolatin5'               => 'ISO-8859-9',
        'csisolatin6'               => 'ISO-8859-10',
        'csisolatin9'               => 'ISO-8859-15',
        'csisolatinarabic'          => 'ISO-8859-6',
        'csisolatincyrillic'        => 'ISO-8859-5',
        'csisolatingreek'           => 'ISO-8859-7',
        'csisolatinhebrew'          => 'ISO-8859-8',
        'cskoi8r'                   => 'KOI8-R',
        'csksc56011987'             => 'EUC-KR',
        'csmacintosh'               => 'x-mac-roman',
        'csshiftjis'                => 'Shift_JIS',
        'csueckr'                   => 'EUC-KR',
        'csunicode'                 => 'UTF-16BE',
        'csunicode11'               => 'UTF-16BE',
        'csunicode11utf7'           => 'UTF-7',
        'csunicodeascii'            => 'UTF-16BE',
        'csunicodelatin1'           => 'UTF-16BE',
        'csviqr'                    => 'VIQR',
        'csviscii'                  => 'VISCII',
        'cyrillic'                  => 'ISO-8859-5',
        'dos-874'                   => 'windows-874',
        'ecma-114'                  => 'ISO-8859-6',
        'ecma-118'                  => 'ISO-8859-7',
        'ecma-cyrillic'             => 'ISO-IR-111',
        'elot_928'                  => 'ISO-8859-7',
        'gb_2312'                   => 'GB2312',
        'gb_2312-80'                => 'GB2312',
        'greek'                     => 'ISO-8859-7',
        'greek8'                    => 'ISO-8859-7',
        'hebrew'                    => 'ISO-8859-8',
        'ibm-864'                   => 'IBM864',
        'ibm-864i'                  => 'IBM864i',
        'ibm819'                    => 'ISO-8859-1',
        'ibm874'                    => 'windows-874',
        'iso-10646'                 => 'UTF-16BE',
        'iso-10646-j-1'             => 'UTF-16BE',
        'iso-10646-ucs-2'           => 'UTF-16BE',
        'iso-10646-ucs-4'           => 'UTF-32BE',
        'iso-10646-ucs-basic'       => 'UTF-16BE',
        'iso-10646-unicode-latin1'  => 'UTF-16BE',
        'iso-2022-cn-ext'           => 'ISO-2022-CN',
        'iso-2022-jp-2'             => 'ISO-2022-JP',
        'iso-8859-8i'               => 'ISO-8859-8-I',
        'iso-ir-100'                => 'ISO-8859-1',
        'iso-ir-101'                => 'ISO-8859-2',
        'iso-ir-103'                => 'T.61-8bit',
        'iso-ir-109'                => 'ISO-8859-3',
        'iso-ir-110'                => 'ISO-8859-4',
        'iso-ir-126'                => 'ISO-8859-7',
        'iso-ir-127'                => 'ISO-8859-6',
        'iso-ir-138'                => 'ISO-8859-8',
        'iso-ir-144'                => 'ISO-8859-5',
        'iso-ir-148'                => 'ISO-8859-9',
        'iso-ir-149'                => 'EUC-KR',
        'iso-ir-157'                => 'ISO-8859-10',
        'iso-ir-58'                 => 'GB2312',
        'iso8859-1'                 => 'ISO-8859-1',
        'iso8859-10'                => 'ISO-8859-10',
        'iso8859-11'                => 'ISO-8859-11',
        'iso8859-13'                => 'ISO-8859-13',
        'iso8859-14'                => 'ISO-8859-14',
        'iso8859-15'                => 'ISO-8859-15',
        'iso8859-2'                 => 'ISO-8859-2',
        'iso8859-3'                 => 'ISO-8859-3',
        'iso8859-4'                 => 'ISO-8859-4',
        'iso8859-5'                 => 'ISO-8859-5',
        'iso8859-6'                 => 'ISO-8859-6',
        'iso8859-7'                 => 'ISO-8859-7',
        'iso8859-8'                 => 'ISO-8859-8',
        'iso8859-9'                 => 'ISO-8859-9',
        'iso88591'                  => 'ISO-8859-1',
        'iso885910'                 => 'ISO-8859-10',
        'iso885911'                 => 'ISO-8859-11',
        'iso885912'                 => 'ISO-8859-12',
        'iso885913'                 => 'ISO-8859-13',
        'iso885914'                 => 'ISO-8859-14',
        'iso885915'                 => 'ISO-8859-15',
        'iso88592'                  => 'ISO-8859-2',
        'iso88593'                  => 'ISO-8859-3',
        'iso88594'                  => 'ISO-8859-4',
        'iso88595'                  => 'ISO-8859-5',
        'iso88596'                  => 'ISO-8859-6',
        'iso88597'                  => 'ISO-8859-7',
        'iso88598'                  => 'ISO-8859-8',
        'iso88599'                  => 'ISO-8859-9',
        'iso_8859-1'                => 'ISO-8859-1',
        'iso_8859-15'               => 'ISO-8859-15',
        'iso_8859-1:1987'           => 'ISO-8859-1',
        'iso_8859-2'                => 'ISO-8859-2',
        'iso_8859-2:1987'           => 'ISO-8859-2',
        'iso_8859-3'                => 'ISO-8859-3',
        'iso_8859-3:1988'           => 'ISO-8859-3',
        'iso_8859-4'                => 'ISO-8859-4',
        'iso_8859-4:1988'           => 'ISO-8859-4',
        'iso_8859-5'                => 'ISO-8859-5',
        'iso_8859-5:1988'           => 'ISO-8859-5',
        'iso_8859-6'                => 'ISO-8859-6',
        'iso_8859-6:1987'           => 'ISO-8859-6',
        'iso_8859-7'                => 'ISO-8859-7',
        'iso_8859-7:1987'           => 'ISO-8859-7',
        'iso_8859-8'                => 'ISO-8859-8',
        'iso_8859-8:1988'           => 'ISO-8859-8',
        'iso_8859-9'                => 'ISO-8859-9',
        'iso_8859-9:1989'           => 'ISO-8859-9',
        'koi'                       => 'KOI8-R',
        'koi8'                      => 'KOI8-R',
        'koi8-ru'                   => 'KOI8-U',
        'koi8_r'                    => 'KOI8-R',
        'korean'                    => 'EUC-KR',
        'ks_c_5601-1987'            => 'EUC-KR',
        'ks_c_5601-1989'            => 'EUC-KR',
        'ksc5601'                   => 'EUC-KR',
        'ksc_5601'                  => 'EUC-KR',
        'l1'                        => 'ISO-8859-1',
        'l2'                        => 'ISO-8859-2',
        'l3'                        => 'ISO-8859-3',
        'l4'                        => 'ISO-8859-4',
        'l5'                        => 'ISO-8859-9',
        'l6'                        => 'ISO-8859-10',
        'l9'                        => 'ISO-8859-15',
        'latin1'                    => 'ISO-8859-1',
        'latin2'                    => 'ISO-8859-2',
        'latin3'                    => 'ISO-8859-3',
        'latin4'                    => 'ISO-8859-4',
        'latin5'                    => 'ISO-8859-9',
        'latin6'                    => 'ISO-8859-10',
        'logical'                   => 'ISO-8859-8-I',
        'mac'                       => 'x-mac-roman',
        'macintosh'                 => 'x-mac-roman',
        'ms932'                     => 'Shift_JIS',
        'ms_kanji'                  => 'Shift_JIS',
        'shift-jis'                 => 'Shift_JIS',
        'sjis'                      => 'Shift_JIS',
        'sun_eu_greek'              => 'ISO-8859-7',
        't.61'                      => 'T.61-8bit',
        'tis620'                    => 'TIS-620',
        'unicode-1-1-utf-7'         => 'UTF-7',
        'unicode-1-1-utf-8'         => 'UTF-8',
        'unicode-2-0-utf-7'         => 'UTF-7',
        'visual'                    => 'ISO-8859-8',
        'windows-31j'               => 'Shift_JIS',
        'windows-949'               => 'EUC-KR',
        'x-cp1250'                  => 'windows-1250',
        'x-cp1251'                  => 'windows-1251',
        'x-cp1252'                  => 'windows-1252',
        'x-cp1253'                  => 'windows-1253',
        'x-cp1254'                  => 'windows-1254',
        'x-cp1255'                  => 'windows-1255',
        'x-cp1256'                  => 'windows-1256',
        'x-cp1257'                  => 'windows-1257',
        'x-cp1258'                  => 'windows-1258',
        'x-euc-jp'                  => 'EUC-JP',
        'x-gbk'                     => 'gbk',
        'x-iso-10646-ucs-2-be'      => 'UTF-16BE',
        'x-iso-10646-ucs-2-le'      => 'UTF-16LE',
        'x-iso-10646-ucs-4-be'      => 'UTF-32BE',
        'x-iso-10646-ucs-4-le'      => 'UTF-32LE',
        'x-sjis'                    => 'Shift_JIS',
        'x-unicode-2-0-utf-7'       => 'UTF-7',
        'x-x-big5'                  => 'Big5',
        'zh_cn.euc'                 => 'GB2312',
        'zh_tw-big5'                => 'Big5',
        'zh_tw-euc'                 => 'x-euc-tw',
    ];

    /**
     * Decode text to UTF-8.
     *
     * @param string $text        Text to decode
     * @param string $fromCharset Original charset
     */
    public static function decode(string $text, string $fromCharset): string
    {
        static $utf8Aliases = [
            'unicode-1-1-utf-8' => true,
            'utf8'              => true,
            'utf-8'             => true,
            'UTF8'              => true,
            'UTF-8'             => true,
        ];

        if (isset($utf8Aliases[$fromCharset])) {
            return $text;
        }

        $originalFromCharset  = $fromCharset;
        $lowercaseFromCharset = \strtolower($fromCharset);
        if (isset(self::$charsetAliases[$lowercaseFromCharset])) {
            $fromCharset = self::$charsetAliases[$lowercaseFromCharset];
        }

        \set_error_handler(static function (): bool {
            return true;
        });

        $iconvDecodedText = \iconv($fromCharset, 'UTF-8', $text);
        if (false === $iconvDecodedText) {
            $iconvDecodedText = \iconv($originalFromCharset, 'UTF-8', $text);
        }

        \restore_error_handler();

        if (false !== $iconvDecodedText) {
            return $iconvDecodedText;
        }

        $errorMessage = null;
        $errorNumber  = 0;
        \set_error_handler(static function ($nr, $message) use (&$errorMessage, &$errorNumber): bool {
            $errorMessage = $message;
            $errorNumber = $nr;

            return true;
        });

        $decodedText = \mb_convert_encoding($text, 'UTF-8', $fromCharset);

        \restore_error_handler();

        if (null !== $errorMessage) {
            throw new UnsupportedCharsetException(\sprintf(
                'Unsupported charset "%s"%s: %s',
                $originalFromCharset,
                ($fromCharset !== $originalFromCharset) ? \sprintf(' (alias found: "%s")', $fromCharset) : '',
                $errorMessage
            ), $errorNumber);
        }

        return $decodedText;
    }
}
