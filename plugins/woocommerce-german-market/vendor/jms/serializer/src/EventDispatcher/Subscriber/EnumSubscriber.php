<?php
/**
 * @license MIT
 *
 * Modified by MarketPress GmbH on 16-October-2024 using {@see https://github.com/BrianHenryIE/strauss}.
 */

declare(strict_types=1);

namespace MarketPress\German_Market\JMS\Serializer\EventDispatcher\Subscriber;

use MarketPress\German_Market\JMS\Serializer\EventDispatcher\EventSubscriberInterface;
use MarketPress\German_Market\JMS\Serializer\EventDispatcher\PreSerializeEvent;

final class EnumSubscriber implements EventSubscriberInterface
{
    public function onPreSerializeEnum(PreSerializeEvent $event): void
    {
        $type = $event->getType();

        if (isset($type['name']) && ('enum' === $type['name'] || !is_a($type['name'], \UnitEnum::class, true))) {
            return;
        }

        $object = $event->getObject();
        $params = [get_class($object), $object instanceof \BackedEnum ? 'value' : 'name'];
        $event->setType('enum', $params);
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            ['event' => 'serializer.pre_serialize', 'method' => 'onPreSerializeEnum', 'interface' => \UnitEnum::class],
        ];
    }
}
