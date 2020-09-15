<?php
declare(strict_types=1);

namespace PrimeData\PrimeDataConnect\Model\Tracking;

use Prime\Tracking\Event as PrimeEventSdk;

class PrimeEvent
{
    /**
     * @var string
     */
    private $eventName = 'default';
    /**
     * @var string
     */
    private $scope = 'default';
    /**
     * @var array
     */
    private $properties = [];
    /**
     * @var PrimeTarget
     */
    private $primeTarget;

    protected $eventSdk;

    /**
     * PrimeEvent constructor.
     * @param PrimeTarget $primeTarget
     */
    public function __construct(
        PrimeTarget $primeTarget
    ) {
        $this->primeTarget = $primeTarget;
    }

    /**
     * @param string $scope
     * @return string
     */
    public function setScope(string $scope)
    {
        $this->scope = $scope;
        return $this;
    }

    /**
     * @return string
     */
    public function getScope()
    {
        return $this->scope;
    }

    /**
     * @param string $eventName
     */
    public function setEventName(string $eventName)
    {
        $this->eventName = $eventName;
    }

    /**
     * @return string
     */
    public function getEventName()
    {
        return  $this->eventName;
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
    public function getProperties() :array
    {
        return  $this->properties;
    }

    /**
     * @return PrimeEventSdk
     */
    public function createPrimeEventSdk()
    {
        if ($this->eventSdk instanceof PrimeEventSdk) {
            return  $this->eventSdk;
        }
        $this->eventSdk = new PrimeEventSdk($this->getEventName(), $this->getScope(), $this->getProperties());
        return $this->eventSdk;
    }

    /**
     * @param string $itemId
     * @param string $itemType
     * @param array $data
     */
    public function setTargetEvent(string $itemId, string $itemType, array $data)
    {
        $event = $this->createPrimeEventSdk();
        $this->primeTarget->setItemId($itemId);
        $this->primeTarget->setItemType($itemType);
        $this->primeTarget->setProperties($data);
        $primeTarget = $this->primeTarget->createPrimeTarget();
        $event::withTarget($primeTarget);
    }

    /**
     * @param string $sessionId
     * @return $this
     */
    public function setSessionId(string $sessionId)
    {
        $event = $this->createPrimeEventSdk();
        $event::withSessionID($sessionId);

        return $this;
    }
}
