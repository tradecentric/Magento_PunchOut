<?php
declare(strict_types=1);

namespace Punchout2Go\Punchout\Model\PunchoutSessionCollector\CustomerHandler;

use Magento\Framework\Exception\LocalizedException as SessionException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class CustomerService
 * @package Punchout2Go\Punchout\Model\PunchoutSessionCollector\CustomerHandler
 */
class CustomerService
{
    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var \Magento\Customer\Api\AccountManagementInterface
     */
    protected $accountManagement;

    /**
     * @var CustomerExtractor
     */
    protected $customerExtractor;

    /**
     * @var GroupByCodeProvider
     */
    protected $groupByCodeProvider;

    /**
     * CustomerService constructor.
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param CustomerExtractor $customerExtractor
     * @param GroupByCodeProvider $groupByCodeProvider
     * @param \Magento\Customer\Api\AccountManagementInterface $accountManagement
     */
    public function __construct(
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        CustomerExtractor $customerExtractor,
        GroupByCodeProvider $groupByCodeProvider,
        \Magento\Customer\Api\AccountManagementInterface $accountManagement
    ) {
        $this->customerRepository = $customerRepository;
        $this->accountManagement = $accountManagement;
        $this->eventManager = $eventManager;
        $this->customerExtractor = $customerExtractor;
        $this->groupByCodeProvider = $groupByCodeProvider;
    }

    /**
     * @param string $customerEmail
     * @param array $customerData
     */
    public function loadCustomer(string $customerEmail)
    {
        $this->eventManager->dispatch('punchout_load_customer', [
            'email' => $customerEmail
        ]);
        try {
            $customer = $this->customerRepository->get($customerEmail);
        } catch (NoSuchEntityException $e) {
            return null;
        }
        return $customer;
    }

    /**
     * @param array $params
     * @return \Magento\Customer\Api\Data\CustomerInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function createCustomer(array $params)
    {
        $this->eventManager->dispatch('punchout_new_customer_before_save', [
            'customer_params' => $params
        ]);

        $customerEntity = $this->createCustomerEntity($params);
        $customer = $this->accountManagement->createAccount($customerEntity);

        $this->eventManager->dispatch('punchout_new_customer_after_save', [
            'customer' => $customer
        ]);

        return $customer;
    }

    /**
     * @param \Magento\Customer\Api\Data\CustomerInterface $customer
     * @param array $params
     * @throws SessionException
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\State\InputMismatchException
     */
    public function updateCustomer(\Magento\Customer\Api\Data\CustomerInterface $customer, array $params = [])
    {
        $modifiedCustomer = $this->createCustomerEntity($params)->__toArray();
        unset($modifiedCustomer['created_at']);
        array_walk( $modifiedCustomer, function(&$value, $key) use ($customer) {
            if ($key == 'custom_attributes') {
                foreach ($value as $attribute) {
                    $customer->setCustomAttribute($attribute['attribute_code'], $attribute['value']);
                }
            } else {
                $customer->setData($key, $value);
            }
        });
        $this->customerRepository->save($customer);
    }

    /**
     * @param array $params
     * @return \Magento\Customer\Api\Data\CustomerInterface
     * @throws SessionException
     */
    protected function createCustomerEntity(array $params)
    {
        $customer = $this->customerExtractor->extract(
            'customer_account_create',
            $params
        );
        $customer->setCreatedAt((new \DateTime())->getTimestamp());
        if (!isset($params['default_group'])) {
            return $customer;
        }
        $group = $this->groupByCodeProvider->getGroupByCode($params['default_group']);
        if (!$group) {
            return $customer;
        }
        $customer->setGroupId($group->getId());
        return $customer;
    }
}
