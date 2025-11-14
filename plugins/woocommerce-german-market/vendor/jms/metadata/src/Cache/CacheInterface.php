<?php
/**
 * @license MIT
 *
 * Modified by MarketPress GmbH on 16-October-2024 using {@see https://github.com/BrianHenryIE/strauss}.
 */

declare(strict_types=1);

namespace MarketPress\German_Market\Metadata\Cache;

use MarketPress\German_Market\Metadata\ClassMetadata;

interface CacheInterface
{
    /**
     * Loads a class metadata instance from the cache
     */
    public function load(string $class): ?ClassMetadata;

    /**
     * Puts a class metadata instance into the cache
     */
    public function put(ClassMetadata $metadata): void;

    /**
     * Evicts the class metadata for the given class from the cache.
     */
    public function evict(string $class): void;
}
