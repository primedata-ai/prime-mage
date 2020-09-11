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
    )
    {
        $this->primeTarget = $primeTarget;
    }

    /**
     * @param string $scope
     */
    public function setScope(string $scope)
    {
        $this->scope = $scope;
    }

    /**
     * @return string
     */
    public function getScope()
    {
        $result = $this->scope;
        $this->scope = 'default';

        return $result;
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
        $result = $this->eventName;
        $this->eventName = 'default';

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
    public function getProperties() :array
    {
        $result = $this->properties;
        $this->properties = [];
        return $result;
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
     */
    public function setSessionId(string $sessionId)
    {
        $event = $this->createPrimeEventSdk();
        $event::withSessionID($sessionId);
    }
}
