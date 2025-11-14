<?php
/**
 * @license MIT
 *
 * Modified by MarketPress GmbH on 16-October-2024 using {@see https://github.com/BrianHenryIE/strauss}.
 */

declare(strict_types=1);

namespace MarketPress\German_Market\Metadata\Driver;

interface FileLocatorInterface
{
    public function findFileForClass(\ReflectionClass $class, string $extension): ?string;
}
