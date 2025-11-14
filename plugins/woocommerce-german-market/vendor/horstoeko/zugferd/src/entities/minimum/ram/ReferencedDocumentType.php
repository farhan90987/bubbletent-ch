<?php
/**
 * @license MIT
 *
 * Modified by MarketPress GmbH on 16-October-2024 using {@see https://github.com/BrianHenryIE/strauss}.
 */

namespace MarketPress\German_Market\horstoeko\zugferd\entities\minimum\ram;

/**
 * Class representing ReferencedDocumentType
 *
 * XSD Type: ReferencedDocumentType
 */
class ReferencedDocumentType
{

    /**
     * @var \MarketPress\German_Market\horstoeko\zugferd\entities\minimum\udt\IDType $issuerAssignedID
     */
    private $issuerAssignedID = null;

    /**
     * Gets as issuerAssignedID
     *
     * @return \MarketPress\German_Market\horstoeko\zugferd\entities\minimum\udt\IDType
     */
    public function getIssuerAssignedID()
    {
        return $this->issuerAssignedID;
    }

    /**
     * Sets a new issuerAssignedID
     *
     * @param  \MarketPress\German_Market\horstoeko\zugferd\entities\minimum\udt\IDType $issuerAssignedID
     * @return self
     */
    public function setIssuerAssignedID(\MarketPress\German_Market\horstoeko\zugferd\entities\minimum\udt\IDType $issuerAssignedID)
    {
        $this->issuerAssignedID = $issuerAssignedID;
        return $this;
    }
}
