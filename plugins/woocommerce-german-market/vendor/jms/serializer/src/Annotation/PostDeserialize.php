<?php
/**
 * @license MIT
 *
 * Modified by MarketPress GmbH on 16-October-2024 using {@see https://github.com/BrianHenryIE/strauss}.
 */

declare(strict_types=1);

namespace MarketPress\German_Market\JMS\Serializer\Annotation;

/**
 * This annotation can be defined on methods which are called after the
 * deserialization of the object is complete.
 *
 * These methods do not necessarily have to be public.
 *
 * @Annotation
 * @Target("METHOD")
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
#[\Attribute(\Attribute::TARGET_METHOD)]
final class PostDeserialize implements SerializerAttribute
{
}
