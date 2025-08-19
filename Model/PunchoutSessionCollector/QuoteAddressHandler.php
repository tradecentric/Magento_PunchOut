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
		$addressData = [
		    "company" => "tradecentric",
			"street" => "123 Main Street",
			"city" => "Nashville",
			"[postcode" => "37076",
			"telephone" => "",
			"region" => 19,
			"country_id" => "US"
		];
		$this->logger->log(print_r($addressData, true));
		
        $address = $object->getQuote()->getShippingAddress();
		
        $address->setSameAsBilling(0);
        $address->setCustomerId($object->getCustomer()->getId());
        $address->setEmail($object->getCustomer()->getEmail());
        $address->addData($addressData);
        $address->setCollectShippingRates(false);
		
		$this->logger->log(print_r($address, true));
		$this->logger->log(print_r($object->getQuote(), true));

        $this->logger->log(sprintf("Saving address data quite_id %d : address_id %d ", $object->getQuote()->getId(), $address->getId()));
		$this->logger->log(sprintf('Saving address data customer_id %d : address_id %d ', $address->getCustomerId(), $address->getAddressId()));
        $this->logger->log('Quote Address Setup Complete');
    }
}