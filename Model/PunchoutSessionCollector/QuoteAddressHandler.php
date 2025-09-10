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
		$this->logger->log('isAddressToCart: ' . $this->helper->isAddressToCart());
		$this->logger->log('isMageAddressToCart: ' . $this->helper->isMageAddressToCart());
        if ($this->helper->isAddressToCart() == null) {
            $this->logger->log('Create address disabled');
            return;
        }
		if ($this->helper->isMageAddressToCart()) {
            $this->logger->log('Create from Mage address enabled');
            return;
        }

        $addressData = $this->dataExtractor->extract($object->getSession()->getParams());
        $address = $object->getQuote()->getShippingAddress();
        $address->setSameAsBilling(0);
        $address->setCustomerId($object->getCustomer()->getId());
        $address->setEmail($object->getCustomer()->getEmail());
        $address->addData($addressData);
        $address->setCollectShippingRates(false);
        $this->logger->log(sprintf("QuoteAddressHandler.handle() - Saving address data quoteId() %s : address->getCustomerId() %s ", $object->getQuote()->getId(), $address->getCustomerId()));
        $this->logger->log('Quote Address Setup Complete');
    }
}