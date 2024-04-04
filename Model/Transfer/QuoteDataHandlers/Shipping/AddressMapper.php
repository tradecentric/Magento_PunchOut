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
     * AddressMapper constructor.
     * @param array $addressMap
     */
    public function __construct(array $addressMap = [])
    {
        $this->addressMap = $addressMap;
    }

    /**
     * @param \Magento\Quote\Api\Data\AddressInterface $address
     * @return mixed[]
     */
    public function getAddressData(\Magento\Quote\Api\Data\AddressInterface $address)
    {
        $result = $address->getData();
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
