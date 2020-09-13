<?php
declare(strict_types=1);

namespace PrimeData\PrimeDataConnect\Model\ProcessData;

use Magento\Customer\Model\Customer;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;

class CustomerHandle
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Session
     */
    private $customerSession;

    /**
     * @var CustomerFactory
     */
    private $customerFactory;

    /**
     * ProductHandle constructor.
     * @param StoreManagerInterface $storeManager
     * @param CustomerFactory $customerFactory
     * @param Session $customerSession
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        CustomerFactory $customerFactory,
        Session $customerSession
    ) {
        $this->storeManager = $storeManager;
        $this->customerSession = $customerSession;
        $this->customerFactory = $customerFactory;
    }

    /**
     * @param Customer $customer
     * @return array
     * @throws NoSuchEntityException
     */
    public function processCustomerSync(Customer $customer)
    {
        $email = $customer->getEmail();
        $customerID = $customer->getId();
        $firstName = $customer->getFirstname();
        $lastName = $customer->getLastname();
        $joinedAt = $customer->getCreatedAt();
        $genderStatus = $customer->getGender();
        $dob = $customer->getDob();

        $customerDob = $this->convertBirthDate((string)$dob);
        $customerGender = $this->convertGender((string)$genderStatus);
        $customerData = array_merge($customerDob, $customerGender);
        $customerData['email'] = $email;
        $customerData['customer_id'] = $customerID;
        $customerData['firstName'] = $firstName;
        $customerData['lastName'] = $lastName;
        $customerData['joined_at'] = $joinedAt;
        $customerData['websiteId'] = $this->getWebsiteId();
        $customerData['store_url'] = $this->getStoreUrl();

        return $customerData;
    }

    /**
     * @param string $dob
     * @return array
     */
    protected function convertBirthDate(string $dob) :array
    {
        $customerData = [];
        if (!$dob) {
            $customerData['birth_day'] = null;
            $customerData['birth_month']  = null;
            $customerData['birth_year'] = null;
            return  $customerData;
        }

        $time  = strtotime($dob);
        $customerData['birth_day']    = date('d', $time) ? date('d', $time) : '';
        $customerData['birth_month']  = date('m', $time) ? date('m', $time) : '';
        $customerData['birth_year']   = date('Y', $time) ? date('Y', $time) : '';

        return $customerData;
    }

    /**
     * @param string $gender
     * @return array
     */
    protected function convertGender(string $gender)
    {
        $customerData = [];
        if (null == $gender) {
            $customerData['gender'] = null;
            return $customerData;
        }

        if ($gender == 1) {
            $customerData['gender'] = 'male';
        } else {
            $customerData['gender'] = 'female';
        }

        return  $customerData;
    }

    /**
     * @return mixed
     * @throws NoSuchEntityException
     */
    protected function getStoreUrl()
    {
        $store = $this->storeManager->getStore();
        return $store->getBaseUrl();
    }

    /**
     * @return int
     * @throws NoSuchEntityException
     */
    protected function getWebsiteId()
    {
        return $this->storeManager->getStore()->getWebsiteId();
    }

    /**
     * @param int $customerId
     * @return string|null
     */
    public function getCustomerSessionId(int $customerId)
    {
        $customerIdSession = $this->customerSession->getSessionId();
        if (!$customerIdSession) {
            return null;
        }

        if ($customerId === (int)$customerIdSession) {
            return $this->customerSession->getSessionId();
        }

        return null;
    }

    /**
     * @param Customer $customer
     * @return array
     */
    public function getCustomerAddress(Customer $customer)
    {
        $customerData = [];
        $customerAddress = $customer->getAddresses();
        if (count($customerAddress) == 0) {
            return $customerData;
        }

        foreach ($customerAddress as $key => $address) {
            $customerData['address'][] = $address->toArray();
        }

        return  $customerData;
    }

    /**
     * @param string $email
     * @return Customer
     * @throws LocalizedException
     */
    public function getCustomerByEmail(string $email)
    {
        $customerModel = $this->customerFactory->create();
        $websiteId = $this->getWebsiteId();
        $customerModel->setWebsiteId($websiteId);
        return $customerModel->loadByEmail($email);
    }
}
