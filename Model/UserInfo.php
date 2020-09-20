<?php


namespace PrimeData\PrimeDataConnect\Model;


class UserInfo
{
    private $userID;
    private $sessionID;

    /**
     * @return mixed
     */
    public function getUserID()
    {
        return $this->userID;
    }

    /**
     * @return mixed
     */
    public function getSessionID()
    {
        return $this->sessionID;
    }

    /**
     * UserInfo constructor.
     * @param $userID
     * @param $sessionID
     */
    public function __construct($userID, $sessionID)
    {
        $this->userID = $userID;
        $this->sessionID = $sessionID;
    }
}