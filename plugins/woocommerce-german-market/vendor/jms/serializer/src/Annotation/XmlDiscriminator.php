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
 * @Target("CLASS")
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
class XmlDiscriminator implements SerializerAttribute
{
    use AnnotationUtilsTrait;

    /**
     * @var bool
     */
    public $attribute = false;

    /**
     * @var bool
     */
    public $cdata = true;

    /**
     * @var string|null
     */
    public $namespace = null;

    public function __construct(array $values = [], bool $attribute = false, bool $cdata = false, ?string $namespace = null)
    {
        $this->loadAnnotationParameters(get_defined_vars());
    }
}
