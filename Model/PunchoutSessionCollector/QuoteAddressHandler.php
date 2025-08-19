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
        $customeraddresses = $customer->getAddresses();
        
		if ($customerAddress) {
			// pull Customer Address Data
			$addressData = getCustomerAddressData($customeraddresses, 'shipping');
			if ($addressData) {
				$this->logger->log('Logging customer shipping address data');
				$this->logger->log(print_r($addressData, true));
			}
		
			$addressData = getCustomerAddressData($customeraddresses, 'billing');
			if ($addressData) {
				$this->logger->log('Logging customer billing address data');
				$this->logger->log(print_r($addressData, true));				
			}
		}
		
 //       $address->addData($addressData);
 //       $address->setCollectShippingRates(false);
        
//      $this->logger->log('Logging Shipping address data - after addData');
//      $this->logger->log(print_r($address, true));

//      $this->logger->log('Logging quote data');
//      $this->logger->log(print_r($quote, true))
        
        /** @var \Magento\Quote\Api\CartRepositoryInterface $quoteRepository */
//      $this->quoteRepository->save($quote);
        
        $this->logger->log(sprintf('Saving address data customer_id %d : customer_address_id %d ', $address->getCustomerId(), $address->getCustomerAddressId()));
        $this->logger->log('Quote Address Setup Complete');
    }

	/**
     * @param Magento\Customer\Api\Data\AddressInterface $cuatomerId
     * @param string $type 
     * return $addressData array	 
     */
	private function getCustomerAddressData(Magento\Customer\Api\Data\AddressInterface $customerAddresses, $type = 'shipping')
    {
        $addressData = "";
		// get Customer Shipping Address Data
		foreach ($customerAddresses as $customerAddress) {
			if ($customerAddress->getIsDefaultShipping() && $type === 'shipping') {            
				// Get Customer Shipping Address data
				$addressData = [
					'addtress_type' => 'shipping',
					'same_as_billing' => 0,
					'customer_id' => $customeraddress->getId(),
					'firstname' => $customerAddress->getFirstName(),
					'middlename'=> $customeraddress->getMiddleName()),
					'lastname'	=> $customerAddress->getLastname(),
					'prefix'	=> $customerAddress->getPrefix(),
					'suffix'	=> $customerAddress->getSuffix(),
					'company'	=> $customeraddress->getCompany(),
					'street'	=> $customerAddress->getStreet(),
					'city'		=> $customerAddress->getCity(),
					'telephone'	=> $customerAddress->getTelephone()
				];
			} else if ($customeraddress->GetIsDefaultBilling() && $type === 'billing') {
				// Get Customer Billing Address data
				$addressData = [
					'addtress_type' => 'billing',
					'customer_id' => $customeraddress->getId(),
					'firstname' => $customerAddress->getFirstName(),
					'middlename'=> $customeraddress->getMiddleName()),
					'lastname'	=> $customerAddress->getLastname(),
					'prefix'	=> $customerAddress->getPrefix(),
					'suffix'	=> $customerAddress->getSuffix(),
					'company'	=> $customeraddress->getCompany(),
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
	private function updateSessionQuoteAddress(array $addressData, $type = 'shipping')
    {
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->checkoutSession->getQuote();

        if (!$quote->getId()) {
            throw new \Exception("No active quote found in session.");
        }

        // Update either shipping or billing address
        $address = ($type === 'shipping')
            ? $quote->getShippingAddress()
            : $quote->getBillingAddress();

        $address->addData($addressData);

        if ($type === 'shipping') {
            $address->setCollectShippingRates(true);
        }

        // Save quote to DB
        $this->cartRepository->save($quote);

        // Update session container with the fresh quote
        $this->checkoutSession->replaceQuote($quote)->unsLastRealOrderId();

        return $quote;
    }


    /**
     * @param quoteId string
	 * @param addressData array
     * @param type string	 
     */
	private function updateQuoteAddress($quoteId, $addressData, $type = 'shipping')
    {
        // Load the quote
        $quote = $this->quoteFactory->create()->load($quoteId);

        if (!$quote->getId()) {
            throw new \Exception("Quote not found with ID: $quoteId");
        }

        // Get existing address (shipping or billing)
        if ($type === 'shipping') {
            $address = $quote->getShippingAddress();
        } else {
            $address = $quote->getBillingAddress();
        }

        // Update address fields
        $address->addData($addressData);

        // Mark as needing shipping rate recollection (if shipping)
        if ($type === 'shipping') {
            $address->setCollectShippingRates(true);
        }

        // Save quote
        $this->cartRepository->save($quote);

        return $quote;
    }
}
