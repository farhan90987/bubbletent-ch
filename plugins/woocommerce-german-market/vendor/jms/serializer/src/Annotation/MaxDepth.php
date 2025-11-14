<?php
/**
 * @license MIT
 *
 * Modified by MarketPress GmbH on 16-October-2024 using {@see https://github.com/BrianHenryIE/strauss}.
 */

declare(strict_types=1);

namespace MarketPress\German_Market\JMS\Serializer\Annotation;

/**
 * @Annotation
 * @Target({"PROPERTY","METHOD","ANNOTATION"})
 */
#[\Attribute(\Attribute::TARGET_METHOD | \Attribute::TARGET_PROPERTY)]
final class MaxDepth implements SerializerAttribute
{
    use AnnotationUtilsTrait;

    /**
     * @Required
     * @var int
     */
    public $depth;

    public function __construct($values = [], int $depth = 0)
    {
        $this->loadAnnotationParameters(get_defined_vars());
    }
}
