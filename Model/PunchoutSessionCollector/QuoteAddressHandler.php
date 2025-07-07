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
     * QuoteAddressHandler constructor.
     * @param \Punchout2Go\Punchout\Helper\Data $helper
     * @param \Punchout2Go\Punchout\Api\LoggerInterface $logger
     * @param \Punchout2Go\Punchout\Model\DataExtractorInterface $dataExtractor
     */
    public function __construct(
        \Punchout2Go\Punchout\Helper\Data $helper,
        \Punchout2Go\Punchout\Api\LoggerInterface $logger,
        \Punchout2Go\Punchout\Model\DataExtractorInterface $dataExtractor
    ) {
        $this->dataExtractor = $dataExtractor;
        $this->logger = $logger;
        $this->helper = $helper;
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
		$customerShipping = $object->getCustomer()->getShippingAddress();
		$this->logger->log('Logging Customer Shipping Address data');
		$this->logger->log(print_r($customerShipping, true));
		
//		$address->setCustomerAddressId($customerShipping->getId());
//		$address->setMiddleName('Bob');
//		$address->setLastName('Bob');
//		$address->setPrefix('Bob');
//		$address->setSuffix('Bob');
//		$address->setCompany('Bob');
//		$address->setStreet('Bob');
//		$address->setCity('Bob');
//		$address->setTelephone('67855599999');
		
//		$this->logger->log('Logging Shipping address data');
//		$this->logger->log(print_r($address, true));
		
	//	$this->logger->log('Logging SessionContainer object');
	//	$this->logger->log(print_r($object, true));
		
        $address->addData($addressData);
        $address->setCollectShippingRates(false);
        $this->logger->log(sprintf('Saving address data customer_id %d : customer_address_id', $address->getCustomerId(), $address->getCustomerAddressId()));
        $this->logger->log('Quote Address Setup Complete');
    }
}
