<?php
/**
 * @license MIT
 *
 * Modified by MarketPress GmbH on 16-October-2024 using {@see https://github.com/BrianHenryIE/strauss}.
 */

namespace MarketPress\German_Market\horstoeko\zugferd\entities\en16931\ram;

/**
 * Class representing LineTradeDeliveryType
 *
 * XSD Type: LineTradeDeliveryType
 */
class LineTradeDeliveryType
{

    /**
     * @var \MarketPress\German_Market\horstoeko\zugferd\entities\en16931\udt\QuantityType $billedQuantity
     */
    private $billedQuantity = null;

    /**
     * Gets as billedQuantity
     *
     * @return \MarketPress\German_Market\horstoeko\zugferd\entities\en16931\udt\QuantityType
     */
    public function getBilledQuantity()
    {
        return $this->billedQuantity;
    }

    /**
     * Sets a new billedQuantity
     *
     * @param  \MarketPress\German_Market\horstoeko\zugferd\entities\en16931\udt\QuantityType $billedQuantity
     * @return self
     */
    public function setBilledQuantity(\MarketPress\German_Market\horstoeko\zugferd\entities\en16931\udt\QuantityType $billedQuantity)
    {
        $this->billedQuantity = $billedQuantity;
        return $this;
    }
}
