<?php
/**
 * @license MIT
 *
 * Modified by MarketPress GmbH on 16-October-2024 using {@see https://github.com/BrianHenryIE/strauss}.
 */

declare(strict_types=1);

namespace MarketPress\German_Market\Metadata\Driver;

/**
 * Forces advanced logic to drivers.
 *
 * @author Jordan Stout <j@jrdn.org>
 */
interface AdvancedDriverInterface extends DriverInterface
{
    /**
     * Gets all the metadata class names known to this driver.
     *
     * @return string[]
     */
    public function getAllClassNames(): array;
}
