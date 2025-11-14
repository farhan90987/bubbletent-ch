<?php
/**
 * @license MIT
 *
 * Modified by MarketPress GmbH on 16-October-2024 using {@see https://github.com/BrianHenryIE/strauss}.
 */

namespace MarketPress\German_Market\horstoeko\zugferd\entities\en16931\udt;

/**
 * Class representing DateType
 *
 * XSD Type: DateType
 */
class DateType
{

    /**
     * @var \MarketPress\German_Market\horstoeko\zugferd\entities\en16931\udt\DateType\DateStringAType $dateString
     */
    private $dateString = null;

    /**
     * Gets as dateString
     *
     * @return \MarketPress\German_Market\horstoeko\zugferd\entities\en16931\udt\DateType\DateStringAType
     */
    public function getDateString()
    {
        return $this->dateString;
    }

    /**
     * Sets a new dateString
     *
     * @param  \MarketPress\German_Market\horstoeko\zugferd\entities\en16931\udt\DateType\DateStringAType $dateString
     * @return self
     */
    public function setDateString(?\MarketPress\German_Market\horstoeko\zugferd\entities\en16931\udt\DateType\DateStringAType $dateString = null)
    {
        $this->dateString = $dateString;
        return $this;
    }
}
