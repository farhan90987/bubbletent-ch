<?php
/**
 * @license MIT
 *
 * Modified by MarketPress GmbH on 16-October-2024 using {@see https://github.com/BrianHenryIE/strauss}.
 */

declare(strict_types=1);

namespace MarketPress\German_Market\JMS\Serializer\ContextFactory;

use MarketPress\German_Market\JMS\Serializer\SerializationContext;

/**
 * Default Serialization Context Factory.
 */
final class DefaultSerializationContextFactory implements SerializationContextFactoryInterface
{
    public function createSerializationContext(): SerializationContext
    {
        return new SerializationContext();
    }
}
