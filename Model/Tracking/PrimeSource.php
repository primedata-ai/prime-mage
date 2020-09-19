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
    protected $itemId;

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
        return $this->itemId;
    }

    /**
     * @return PrimeSourceSdk
     */
    public function createPrimeSource()
    {
        return new PrimeSourceSdk($this->getItemType(), $this->getItemId(), []);
    }
}
