<?php
/**
 * @license MIT
 *
 * Modified by MarketPress GmbH on 16-October-2024 using {@see https://github.com/BrianHenryIE/strauss}.
 */

namespace MarketPress\German_Market\Sabberworm\CSS\Parsing;

/**
 * Thrown if the CSS parser attempts to print something invalid.
 */
class OutputException extends SourceException
{
    /**
     * @param string $sMessage
     * @param int $iLineNo
     */
    public function __construct($sMessage, $iLineNo = 0)
    {
        parent::__construct($sMessage, $iLineNo);
    }
}
