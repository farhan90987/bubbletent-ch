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

namespace MarketPress\German_Market\Symfony\Component\Validator\Exception;

class MissingOptionsException extends ValidatorException
{
    public function __construct(
        string $message,
        private array $options,
    ) {
        parent::__construct($message);
    }

    public function getOptions(): array
    {
        return $this->options;
    }
}
