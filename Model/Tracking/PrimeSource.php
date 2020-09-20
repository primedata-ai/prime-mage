<?php
declare(strict_types=1);

namespace PrimeData\PrimeDataConnect\Model\Tracking;

use Prime\Tracking\Source as PrimeSourceSdk;

class PrimeSource
{
    /**
     * @var string
     */
    private $itemType;

    /**
     * @var string
     */
    private $itemId;

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
    private function getItemType()
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
    private function getItemId()
    {
        return $this->itemId;
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
    private function getProperties()
    {
        return $this->properties;
    }

    /**
     * @return PrimeSourceSdk
     */
    public function createPrimeSource()
    {
        return new PrimeSourceSdk($this->getItemType(), $this->getItemId(), $this->getProperties());
    }
}
