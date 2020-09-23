<?php
declare(strict_types=1);

namespace PrimeData\PrimeDataConnect\Model\ProcessData;

use Magento\Framework\App\Request\Http;
use Magento\Framework\HTTP\Header;

class DeviceHandle
{
    /**
     * @var Http
     */
    protected $http;

    /**
     * @var Header
     */
    protected $header;

    /**
     * DeviceHandle constructor.
     * @param Http $http
     * @param Header $header
     */
    public function __construct(
        Http $http,
        Header $header
    ) {
        $this->http = $http;
        $this->header = $header;
    }

    /**
     * @return array
     */
    public function getDeviceInfo() :array
    {
        $consentIp = $this->http->getClientIp();
        $consentUserAgent = $this->header->getHttpUserAgent();
        $browserInfo = parse_user_agent($consentUserAgent);

        $deviceData['ip'] = $consentIp ?: null;
        $deviceData['device_type'] = $browserInfo['platform'] ?: null;
        $deviceData['browser'] = $browserInfo['browser'] ?: null;

        return $deviceData;
    }
}
