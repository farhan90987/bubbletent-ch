<?php
/**
 * @license MIT
 *
 * Modified by MarketPress GmbH on 16-October-2024 using {@see https://github.com/BrianHenryIE/strauss}.
 */

declare(strict_types=1);

namespace MarketPress\German_Market\JMS\Serializer;

interface NullAwareVisitorInterface
{
    /**
     * Determine if a value conveys a null value.
     * An example could be an xml element (Dom, SimpleXml, ...) that is tagged with a xsi:nil attribute
     *
     * @param mixed $value
     */
    public function isNull($value): bool;
}
