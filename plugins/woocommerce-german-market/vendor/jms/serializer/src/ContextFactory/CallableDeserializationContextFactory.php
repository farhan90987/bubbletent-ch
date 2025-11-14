<?php
/**
 * @license MIT
 *
 * Modified by MarketPress GmbH on 16-October-2024 using {@see https://github.com/BrianHenryIE/strauss}.
 */

declare(strict_types=1);

namespace MarketPress\German_Market\JMS\Serializer\ContextFactory;

use MarketPress\German_Market\JMS\Serializer\DeserializationContext;

final class CallableDeserializationContextFactory implements
    DeserializationContextFactoryInterface
{
    /**
     * @var callable():DeserializationContext
     */
    private $callable;

    /**
     * @param callable():DeserializationContext $callable
     */
    public function __construct(callable $callable)
    {
        $this->callable = $callable;
    }

    public function createDeserializationContext(): DeserializationContext
    {
        $callable = $this->callable;

        return $callable();
    }
}
