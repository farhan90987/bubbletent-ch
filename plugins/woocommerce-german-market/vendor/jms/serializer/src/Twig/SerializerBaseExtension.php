<?php
/**
 * @license MIT
 *
 * Modified by MarketPress GmbH on 16-October-2024 using {@see https://github.com/BrianHenryIE/strauss}.
 */

declare(strict_types=1);

namespace MarketPress\German_Market\JMS\Serializer\Twig;

use Twig\Extension\AbstractExtension;

abstract class SerializerBaseExtension extends AbstractExtension
{
    /**
     * @var string
     */
    protected $serializationFunctionsPrefix;

    /**
     * @return string
     *
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingReturnTypeHint
     */
    public function getName()
    {
        return 'jms_serializer';
    }

    public function __construct(string $serializationFunctionsPrefix = '')
    {
        $this->serializationFunctionsPrefix = $serializationFunctionsPrefix;
    }
}
