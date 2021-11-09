<?php
declare(strict_types=1);

namespace Punchout2Go\Punchout\Model\PunchoutSessionCollector;

use Magento\Framework\Exception\LocalizedException as SessionException;
use Punchout2Go\Punchout\Api\EntityHandlerInterface;
use Punchout2Go\Punchout\Api\SessionContainerInterface;
use Punchout2Go\Punchout\Model\System\Config\Source\Login;
use Punchout2Go\Punchout\Model\PunchoutSessionCollector\CustomerHandler\CustomerService;

/**
 * Class CustomerHandler
 * @package Punchout2Go\Punchout\Model
 */
class CustomerHandler implements EntityHandlerInterface
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Punchout2Go\Punchout\Model\DataExtractorInterface
     */
    protected $customerDataExtractor;

    /**
     * @var \Punchout2Go\Punchout\Api\LoggerInterface
     */
    protected $logger;

    /**
     * @var \Punchout2Go\Punchout\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager;

    /**
     * @var CustomerService
     */
    protected $customerService;

    /**
     * CustomerHandler constructor.
     * @param \Punchout2Go\Punchout\Api\LoggerInterface $logger
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param CustomerService $customerService
     * @param \Punchout2Go\Punchout\Helper\Data $helper
     * @param \Punchout2Go\Punchout\Model\DataExtractorInterface $customerDataExtractor
     */
    public function __construct(
        \Punchout2Go\Punchout\Api\LoggerInterface $logger,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        CustomerService $customerService,
        \Punchout2Go\Punchout\Helper\Data $helper,
        \Punchout2Go\Punchout\Model\DataExtractorInterface $customerDataExtractor
    ) {
        $this->customerDataExtractor = $customerDataExtractor;
        $this->customerService = $customerService;
        $this->eventManager = $eventManager;
        $this->helper = $helper;
        $this->logger = $logger;
    }

    /**
     * @param SessionContainerInterface $object
     * @return mixed|void
     * @throws SessionException
     */
    public function handle(SessionContainerInterface $object)
    {
        $this->logger->log('Customer Setup Begin');
        if ($this->helper->getCustomerSessionType() != Login::LOGIN_LOGGED_IN) {
            $this->logger->log('Anonymous session');
            return;
        }
        $isCustomerNew = false;
        $customerParams = $this->customerDataExtractor->extract($object->getSession()->getParams());
        $customer = $this->customerService->loadCustomer($customerParams['email']);
        $this->logger->log('Customer loading');

        if (!$customer && $this->helper->isAutoCreateUser()) {
            $this->logger->log('Create new customer');
            $customer = $this->customerService->createCustomer($customerParams);
            $this->logger->log('Create customer');
            $isCustomerNew = true;
        }
        if (!$customer && (bool)$customerParams['default_user']) {
            $customer = $this->customerService->loadCustomer($customerParams['default_user']);
        }

        if (!$customer) {
            throw new SessionException(
                __('Unable to login without a default user.')
            );
        }
        if ($customer && !$isCustomerNew) {
            // update customer params
            $this->customerService->updateCustomer($customer, $customerParams);
        }
        $object->getCustomer()
            ->setId($customer->getId())
            ->setGroupId($customer->getGroupId())
            ->setEmail($customer->getEmail())
            ->setFirstname($customer->getFirstname())
            ->setLastname($customer->getLastname());

        $this->logger->log('Customer Setup Complete');
    }
}
