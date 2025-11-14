<?php
/**
 * @license MIT
 *
 * Modified by MarketPress GmbH on 16-October-2024 using {@see https://github.com/BrianHenryIE/strauss}.
 */

declare(strict_types=1);

namespace MarketPress\German_Market\JMS\Serializer\Exclusion;

use MarketPress\German_Market\JMS\Serializer\Context;
use MarketPress\German_Market\JMS\Serializer\Metadata\ClassMetadata;
use MarketPress\German_Market\JMS\Serializer\Metadata\PropertyMetadata;

/**
 * @author Adrien Brault <adrien.brault@gmail.com>
 */
final class DepthExclusionStrategy implements ExclusionStrategyInterface
{
    public function shouldSkipClass(ClassMetadata $metadata, Context $context): bool
    {
        return $this->isTooDeep($context);
    }

    public function shouldSkipProperty(PropertyMetadata $property, Context $context): bool
    {
        return $this->isTooDeep($context);
    }

    private function isTooDeep(Context $context): bool
    {
        $relativeDepth = 0;

        foreach ($context->getMetadataStack() as $metadata) {
            if (!$metadata instanceof PropertyMetadata) {
                continue;
            }

            $relativeDepth++;

            if (0 === $metadata->maxDepth && $context->getMetadataStack()->top() === $metadata) {
                continue;
            }

            if (null !== $metadata->maxDepth && $relativeDepth > $metadata->maxDepth) {
                return true;
            }
        }

        return false;
    }
}
