<?php
declare(strict_types=1);

namespace Punchout2Go\Punchout\Model\Transfer\QuoteDataHandlers\Shipping;

/**
 * Class AddressMapper
 * @package Punchout2Go\Punchout\Model\Transfer\QuoteDataHandlers\Shipping
 */
class AddressMapper
{
    /**
     * @var array
     */
    protected $addressMap;
	
	/**
     * @var \Punchout2Go\Punchout\Api\LoggerInterface
	 
     */
    protected $logger;

    /**
     * AddressMapper constructor.
     * @param array $addressMap
	 * @param \Punchout2Go\Punchout\Api\LoggerInterface $logger
     */
    public function __construct(
		\Punchout2Go\Punchout\Api\LoggerInterface $logger,
		array $addressMap = []	
	)
    {
        $this->addressMap = $addressMap;
		$this->logger = $logger;
    }

    /**
     * @param \Magento\Quote\Api\Data\AddressInterface $address
     * @return mixed[]
     */
    public function getAddressData(\Magento\Quote\Api\Data\AddressInterface $address)
    {
        $result = $address->getData();
		
		$this->logger->log('AddressInterface Customer Shipping address data');
		$this->logger->log(print_r($result, true));
		
        foreach ($this->addressMap as $destination => $field) {
            if (is_object($field) && ($field instanceof AddressFieldInterface)) {
                $result[$destination] = $field->handle($address);
                continue;
            }
            $result[$destination] = $address->getData($field);
        }
        return $result;
    }
}
