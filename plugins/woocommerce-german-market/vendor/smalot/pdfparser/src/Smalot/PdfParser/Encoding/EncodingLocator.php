<?php
/**
 * @license LGPL-3.0
 *
 * Modified by MarketPress GmbH on 16-October-2024 using {@see https://github.com/BrianHenryIE/strauss}.
 */

namespace MarketPress\German_Market\Smalot\PdfParser\Encoding;

class EncodingLocator
{
    protected static $encodings;

    public static function getEncoding(string $encodingClassName): AbstractEncoding
    {
        if (!isset(self::$encodings[$encodingClassName])) {
            self::$encodings[$encodingClassName] = new $encodingClassName();
        }

        return self::$encodings[$encodingClassName];
    }
}
