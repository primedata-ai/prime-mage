<?php

namespace PrimeData\PrimeDataConnect\Model;

class UserInfo
{
    private $userID;
    private $sessionID;

    /**
     * @return string
     */
    public function getUserID()
    {
        return (string)$this->userID;
    }

    /**
     * @return string
     */
    public function getSessionID()
    {
        return (string)$this->sessionID;
    }

    /**
     * UserInfo constructor.
     * @param string $userID
     * @param string $sessionID
     */
    public function __construct($userID, $sessionID)
    {
        $this->userID = $userID;
        $this->sessionID = $sessionID;
    }
}
