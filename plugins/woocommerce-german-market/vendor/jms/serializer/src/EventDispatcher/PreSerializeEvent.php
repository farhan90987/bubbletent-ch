<?php
/**
 * @license MIT
 *
 * Modified by MarketPress GmbH on 16-October-2024 using {@see https://github.com/BrianHenryIE/strauss}.
 */

declare(strict_types=1);

namespace MarketPress\German_Market\JMS\Serializer\EventDispatcher;

class PreSerializeEvent extends ObjectEvent
{
    /**
     * @param array $params
     */
    public function setType(string $typeName, array $params = []): void
    {
        $this->type = ['name' => $typeName, 'params' => $params];
    }
}
