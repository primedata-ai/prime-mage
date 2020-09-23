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
        return $this->userID;
    }

    /**
     * @return string
     */
    public function getSessionID()
    {
        return $this->sessionID;
    }

    /**
     * UserInfo constructor.
     * @param string $userID
     * @param string $sessionID
     */
    public function __construct(string $userID, string $sessionID)
    {
        $this->userID = $userID;
        $this->sessionID = $sessionID;
    }
}
