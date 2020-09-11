<?php
declare(strict_types=1);

namespace PrimeData\PrimeDataConnect\Model\Tracking;

use Prime\Tracking\Target as PrimeTargetSdk;

class PrimeTarget
{
    /**
     * @var string
     */
    private $itemType;
    /**
     * @var void
     */
    private $itemId;
    /**
     * @var array
     */
    private $properties;

    /**
     * @param string $itemType
     */
    public function setItemType(string $itemType)
    {
        $this->itemType = $itemType;
    }

    /**
     * @return string
     */
    public function getItemType()
    {
        $result = $this->itemType;
        $this->itemType = $this->setItemType('default');
        return $result;
    }

    /**
     * @param string $itemId
     */
    public function setItemId(string $itemId)
    {
        $this->itemId = $itemId;
    }

    /**
     * @return string
     */
    public function getItemId()
    {
        $result = $this->itemtId;
        $this->itemId = $this->setItemId('default');
        return $result;
    }

    /**
     * @param array $properties
     */
    public function setProperties(array $properties)
    {
        $this->properties = $properties;
    }

    /**
     * @return array
     */
    public function getProperties()
    {
        $result = $this->properties;
        $this->properties = $this->setProperties(['default']);
        return $result;
    }

    /**
     * @return PrimeTargetSdk
     */
    public function createPrimeTarget()
    {
        return new PrimeTargetSdk($this->getItemType(), $this->getItemType(), $this->getProperties());
    }
}
