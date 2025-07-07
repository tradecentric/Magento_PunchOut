<?php
declare(strict_types=1);

namespace Punchout2Go\Punchout\Model\PunchoutSessionCollector;

use Punchout2Go\Punchout\Api\EntityHandlerInterface;
use Punchout2Go\Punchout\Api\SessionContainerInterface;

/**
 * Class QuoteAddressHandler
 * @package Punchout2Go\Punchout\Model
 */
class QuoteAddressHandler implements EntityHandlerInterface
{
    /**
     * @var \Punchout2Go\Punchout\Helper\Data
     */
    protected $helper;

    /**
     * @var \Punchout2Go\Punchout\Api\LoggerInterface
     */
    protected $logger;

    /**
     * @var \Punchout2Go\Punchout\Model\DataExtractorInterface
     */
    protected $dataExtractor;
	
	/**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     */
    protected $customerRepository;

    /**
     * QuoteAddressHandler constructor.
     * @param \Punchout2Go\Punchout\Helper\Data $helper
     * @param \Punchout2Go\Punchout\Api\LoggerInterface $logger
     * @param \Punchout2Go\Punchout\Model\DataExtractorInterface $dataExtractor
     */
    public function __construct(
        \Punchout2Go\Punchout\Helper\Data $helper,
        \Punchout2Go\Punchout\Api\LoggerInterface $logger,
        \Punchout2Go\Punchout\Model\DataExtractorInterface $dataExtractor,
		\Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
    ) {
        $this->dataExtractor = $dataExtractor;
        $this->logger = $logger;
        $this->helper = $helper;
		$this->customerRepository = $customerRepository;
    }

    /**
     * @param SessionContainerInterface $object
     */
    public function handle(SessionContainerInterface $object)
    {
        $this->logger->log('Quote Address Setup Begin');
        if (!$this->helper->isAddressToCart()) {
            $this->logger->log('Create address disabled');
            return;
        }
        $addressData = $this->dataExtractor->extract($object->getSession()->getParams());
        $address = $object->getQuote()->getShippingAddress();
        $address->setSameAsBilling(0);
        $address->setCustomerId($object->getCustomer()->getId());
        $address->setEmail($object->getCustomer()->getEmail());
		
		// get Customer Shipping Address Data
		$customer = $this->customerRepository->getById($object->getCustomer()->getId());
		$customeraddresses = $customer->getAddresses();

        foreach ($customerAddresses as $customeraddress) {
            if ($customeraddress->isDefaultShipping()) {
				$this->logger->log('Logging Customer Shipping Address data');
				$this->logger->log(print_r($customeraddress, true));
				
				// Set Quote Shipping Address data
				$address->setCustomerAddressId($customeraddress->getId());
				$address->setFirstName($customeraddress->getFirstName());
				$address->setMiddleName($customeraddress->getMiddleName());
				$address->setLastName($customeraddress->getLastname());
				$address->setPrefix($customeraddress->getPrefix());
				$address->setSuffix($customeraddress->getSuffix());
				$address->setCompany($customeraddress->getCompany());
				$address->setStreet($customeraddress->getStreet());
				$address->setCity($customeraddress->getCity());
				$address->setTelephone($customeraddress->getTelephone());
            }
        }
						
		$this->logger->log('Logging Shipping address data');
		$this->logger->log(print_r($address, true));
		
 //       $address->addData($addressData);
        $address->setCollectShippingRates(false);
        $this->logger->log(sprintf('Saving address data customer_id %d : customer_address_id', $address->getCustomerId(), $address->getCustomerAddressId()));
        $this->logger->log('Quote Address Setup Complete');
    }
}
