<?php
/**
 * @license MIT
 *
 * Modified by MarketPress GmbH on 16-October-2024 using {@see https://github.com/BrianHenryIE/strauss}.
 */

declare(strict_types=1);

namespace MarketPress\German_Market\Metadata\Cache;

/**
 * @author Alexander Strizhak <gam6itko@gmail.com>
 */
interface ClearableCacheInterface
{
    /**
     * Clear all classes metadata from the cache.
     */
    public function clear(): bool;
}
