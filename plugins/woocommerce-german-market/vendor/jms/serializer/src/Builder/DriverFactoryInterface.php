<?php
/**
 * @license MIT
 *
 * Modified by MarketPress GmbH on 16-October-2024 using {@see https://github.com/BrianHenryIE/strauss}.
 */

declare(strict_types=1);

namespace MarketPress\German_Market\JMS\Serializer\Builder;

use Doctrine\Common\Annotations\Reader;
use MarketPress\German_Market\Metadata\Driver\DriverInterface;

interface DriverFactoryInterface
{
    public function createDriver(array $metadataDirs, ?Reader $annotationReader = null): DriverInterface;
}
