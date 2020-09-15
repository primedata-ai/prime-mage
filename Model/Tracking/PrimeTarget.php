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
     * @var string
     */
    protected $itemId;

    /**
     * @var array
     */
    private $properties;

    /**
     * @param string $itemType
     * @return $this
     */
    public function setItemType(string $itemType)
    {
        $this->itemType = $itemType;
        return $this;
    }

    /**
     * @return string
     */
    public function getItemType()
    {
        return $this->itemType;
    }

    /**
     * @param string $itemId
     * @return $this
     */
    public function setItemId(string $itemId)
    {
        $this->itemId = $itemId;
        return $this;
    }

    /**
     * @return string
     */
    public function getItemId()
    {
        return $this->itemtId;
    }

    /**
     * @param array $properties
     * @return $this
     */
    public function setProperties(array $properties)
    {
        $this->properties = $properties;
        return $this;
    }

    /**
     * @return array
     */
    public function getProperties()
    {
        return $this->properties;
    }

    /**
     * @return PrimeTargetSdk
     */
    public function createPrimeTarget()
    {
        return new PrimeTargetSdk($this->getItemType(), $this->getItemType(), $this->getProperties());
    }
}
