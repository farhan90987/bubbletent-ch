<?php
/**
 * @license MIT
 *
 * Modified by MarketPress GmbH on 16-October-2024 using {@see https://github.com/BrianHenryIE/strauss}.
 */

declare(strict_types=1);

namespace MarketPress\German_Market\Metadata\Driver;

interface TraceableFileLocatorInterface extends FileLocatorInterface
{
    /**
     * Finds all possible metadata files for a class
     *
     * @return string[]
     */
    public function getPossibleFilesForClass(\ReflectionClass $class, string $extension): array;
}
