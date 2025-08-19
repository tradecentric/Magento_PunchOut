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
     * @var \Magento\Quote\Api\CartRepositoryInterface $cartRepository
     */
    protected $cartRepository; 

    /**
     * QuoteAddressHandler constructor.
     * @param \Punchout2Go\Punchout\Helper\Data $helper
     * @param \Punchout2Go\Punchout\Api\LoggerInterface $logger
     * @param \Punchout2Go\Punchout\Model\DataExtractorInterface $dataExtractor
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     */
    public function __construct(
        \Punchout2Go\Punchout\Helper\Data $helper,
        \Punchout2Go\Punchout\Api\LoggerInterface $logger,
        \Punchout2Go\Punchout\Model\DataExtractorInterface $dataExtractor,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Quote\Api\CartRepositoryInterface $cartRepository
    ) {
        $this->dataExtractor = $dataExtractor;
        $this->logger = $logger;
        $this->helper = $helper;
        $this->customerRepository = $customerRepository;
        $this->cartRepository = $cartRepository;
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
                
        // get Customer Shipping Address Data
        $customer = $this->customerRepository->getById($object->getCustomer()->getId());
        $customerAddresses = $customer->getAddresses();
        
		if ($customerAddresses) {
			// get Customer Address Data
			$addressData = $this->getCustomerAddressData($customerAddresses, 'shipping');	
			if ($addressData) {
				$this->logger->log('Customer Shipping Address');
				$this->logger->log(print_r($addressData, true));
				$this->updateSessionQuoteAddress($object, $addressData, 'shipping');
			}

			$addressData = $this->getCustomerAddressData($customerAddresses, 'billing');
			if ($addressData) {
				$this->logger->log('Customer Shipping Billing');
				$this->logger->log(print_r($addressData, true));
				$this->updateSessionQuoteAddress($object, $addressData, 'billing');
			}
		}
        $this->logger->log('Quote Address Setup Complete');
    }

	/**
     * @param array $cuatomerId
     * @param string $type 
     * return $addressData array	 
     */
	private function getCustomerAddressData($customerAddresses, $type = 'shipping')
    {
        $addressData = "";
		// get Customer Shipping Address Data
		foreach ($customerAddresses as $customerAddress) {
			if ($customerAddress->isDefaultShipping() && $type === 'shipping') {            
				// Get Customer Shipping Address data
				$addressData = [
					'addtress_type' => 'shipping',
					'same_as_billing' => 0,
					'address_id'=> $customerAddress->getId(),
					'firstname' => $customerAddress->getFirstName(),
					'middlename'=> $customerAddress->getMiddleName(),
					'lastname'	=> $customerAddress->getLastname(),
					'prefix'	=> $customerAddress->getPrefix(),
					'suffix'	=> $customerAddress->getSuffix(),
					'company'	=> $customerAddress->getCompany(),
					'street'	=> $customerAddress->getStreet(),
					'city'		=> $customerAddress->getCity(),
					'telephone'	=> $customerAddress->getTelephone()
				];
			} else if ($customerAddress->isDefaultBilling() && $type === 'billing') {
				// Get Customer Billing Address data
				$addressData = [
					'addtress_type' => 'billing',
					'address_id'=> $customerAddress->getId(),
					'firstname' => $customerAddress->getFirstName(),
					'middlename'=> $customerAddress->getMiddleName(),
					'lastname'	=> $customerAddress->getLastname(),
					'prefix'	=> $customerAddress->getPrefix(),
					'suffix'	=> $customerAddress->getSuffix(),
					'company'	=> $customerAddress->getCompany(),
					'street'	=> $customerAddress->getStreet(),
					'city'		=> $customerAddress->getCity(),
					'telephone'	=> $customerAddress->getTelephone()
				];
			}
		}	
		
		return $addressData;
	}

	/**
     * @param quoteId string
	 * @param addressData array
     * @param type string	 
     */
	private function updateSessionQuoteAddress($object, array $addressData, $type = 'shipping')
    {
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $object->getQuote();

 //       if (!$quote->getId()) {
 //           throw new \Exception("No active quote found in session.");
 //       }
		
		$paramsData = $this->dataExtractor->extract($object->getSession()->getParams());
        $this->logger->log(print_r($paramsData, true));

        // Update either shipping or billing address
        $address = ($type === 'shipping')
            ? $quote->getShippingAddress()
            : $quote->getBillingAddress();

		$address->setCustomerId($object->getCustomer()->getId());
		$address->setEmail($object->getCustomer()->getEmail());
        $address->addData($addressData);

        if ($type === 'shipping') {
            $address->setCollectShippingRates(true);
        }

        // Save quote to DB
        $this->cartRepository->save($quote);

        // Update session container with the fresh quote
        $object->replaceQuote($quote)->unsLastRealOrderId();
		
		$this->logger->log(sprintf('Saving address data customer_id %d : customer_address_id %d ', $address->getCustomerId(), $address->getCustomerAddressId()));
    }
}