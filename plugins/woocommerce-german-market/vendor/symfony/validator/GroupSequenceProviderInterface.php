<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Modified by MarketPress GmbH on 16-October-2024 using {@see https://github.com/BrianHenryIE/strauss}.
 */

namespace MarketPress\German_Market\Symfony\Component\Validator;

use MarketPress\German_Market\Symfony\Component\Validator\Constraints\GroupSequence;

/**
 * Defines the interface for a group sequence provider.
 */
interface GroupSequenceProviderInterface
{
    /**
     * Returns which validation groups should be used for a certain state
     * of the object.
     *
     * @return string[]|string[][]|GroupSequence
     */
    public function getGroupSequence(): array|GroupSequence;
}
