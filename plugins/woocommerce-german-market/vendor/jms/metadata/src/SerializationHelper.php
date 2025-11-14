<?php
/**
 * @license MIT
 *
 * Modified by MarketPress GmbH on 16-October-2024 using {@see https://github.com/BrianHenryIE/strauss}.
 */

declare(strict_types=1);

namespace MarketPress\German_Market\Metadata;

trait SerializationHelper
{
    /**
     * @deprecated Use serializeToArray
     *
     * @return string
     */
    public function serialize()
    {
        return serialize($this->serializeToArray());
    }

    /**
     * @deprecated Use unserializeFromArray
     *
     * @param string $str
     *
     * @return void
     */
    public function unserialize($str)
    {
        $this->unserializeFromArray(unserialize($str));
    }

    public function __serialize(): array
    {
        return [$this->serialize()];
    }

    public function __unserialize(array $data): void
    {
        $this->unserialize($data[0]);
    }
}
