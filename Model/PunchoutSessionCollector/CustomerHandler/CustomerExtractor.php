<?php
declare(strict_types=1);

namespace Punchout2Go\Punchout\Model\PunchoutSessionCollector\CustomerHandler;

use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\GroupManagementInterface;
use Magento\Framework\App\RequestInterface;

/**
 * Customer Extractor model.
 */
class CustomerExtractor
{
    /**
     * @var \Magento\Customer\Model\Metadata\FormFactory
     */
    protected $formFactory;

    /**
     * @var \Magento\Customer\Api\Data\CustomerInterfaceFactory
     */
    protected $customerFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var GroupManagementInterface
     */
    protected $customerGroupManagement;

    /**
     * @var \Magento\Framework\Api\DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @var \Punchout2Go\Punchout\Api\LoggerInterface
     */
    protected $logger;

    /**
     * CustomerExtractor constructor.
     * @param \Magento\Customer\Model\Metadata\FormFactory $formFactory
     * @param \Magento\Customer\Api\Data\CustomerInterfaceFactory $customerFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param GroupManagementInterface $customerGroupManagement
     * @param \Magento\Framework\Api\DataObjectHelper $dataObjectHelper
     * @param \Punchout2Go\Punchout\Api\LoggerInterface $logger
     */
    public function __construct(
        \Magento\Customer\Model\Metadata\FormFactory $formFactory,
        \Magento\Customer\Api\Data\CustomerInterfaceFactory $customerFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        GroupManagementInterface $customerGroupManagement,
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper,
        \Punchout2Go\Punchout\Api\LoggerInterface $logger
    ) {
        $this->formFactory = $formFactory;
        $this->customerFactory = $customerFactory;
        $this->storeManager = $storeManager;
        $this->customerGroupManagement = $customerGroupManagement;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->logger = $logger;
    }

    /**
     * Creates a Customer object populated with the given form code and request data.
     *
     * @param string $formCode
     * @param RequestInterface $request
     * @param array $attributeValues
     * @return CustomerInterface
     */
    public function extract(
        $formCode,
        $requestData,
        array $attributeValues = [],
        array $additionalAttributes = []
    ) {
        $customerForm = $this->formFactory->create(
            CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER,
            $formCode,
            $attributeValues
        );
        $customerForm->setInvisibleIgnored(false);
        $this->logger->log('CustomerExtractor requestData: ' . json_encode($requestData));
        /**
         * Remove false values (fields absent from punchout params) to prevent overwriting valid customer data
         * and avoid triggering stricter DOB validation in 2.4.9+.
         */
        $customerData = array_filter(
            $customerForm->compactData($requestData),
            static function ($value) { return $value !== false; }
        );
        foreach ($additionalAttributes as $attributeCode) {
            $customerData[$attributeCode] = $requestData[$attributeCode] ?? null;
        }
        $allowedAttributes = $customerForm->getAllowedAttributes();
        $isGroupIdEmpty = !isset($allowedAttributes['group_id']);

        $customerDataObject = $this->customerFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $customerDataObject,
            $customerData,
            \Magento\Customer\Api\Data\CustomerInterface::class
        );

        $store = $this->storeManager->getStore();
        $storeId = $store->getId();

        if ($isGroupIdEmpty) {
            $groupId = $customerData['group_id'] ?? $this->customerGroupManagement->getDefaultGroup($storeId)->getId();
            $customerDataObject->setGroupId(
                $groupId
            );
        }

        $customerDataObject->setWebsiteId($store->getWebsiteId());
        $customerDataObject->setStoreId($storeId);

        return $customerDataObject;
    }
}
