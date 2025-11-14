<?php
/**
 * @license MIT
 *
 * Modified by MarketPress GmbH on 16-October-2024 using {@see https://github.com/BrianHenryIE/strauss}.
 */

declare(strict_types=1);

namespace MarketPress\German_Market\JMS\Serializer\Exception;

/**
 * Throw this exception from you custom (de)serialization handler
 * in order to fallback to the default (de)serialization behavior.
 */
class SkipHandlerException extends RuntimeException
{
}
