<?php
declare(strict_types=1);

namespace Punchout2Go\Punchout\Model\Transfer\QuoteDataHandlers;

use Punchout2Go\Punchout\Model\Transfer\QuoteDataHandlerInterface;

/**
 * Class Discount
 * @package Punchout2Go\Punchout\Model\Transfer\QuoteDataHandlers
 */
class Shipping implements QuoteDataHandlerInterface
{
    /**
     * @var \Punchout2Go\Punchout\Helper\Transfer
     */
    protected $helper;

    /**
     * @var \Punchout2Go\Punchout\Helper\Data
     */
    protected $defaultHelper;

    /**
     * @var Shipping\AddressMapper
     */
    protected $addressMapper;

    /**
     * Shipping constructor.
     * @param \Punchout2Go\Punchout\Helper\Transfer $transferHelper
     * @param \Punchout2Go\Punchout\Helper\Data $defaultHelper
     * @param Shipping\AddressMapper $addressMapper
     */
    public function __construct(
        \Punchout2Go\Punchout\Helper\Transfer $transferHelper,
        \Punchout2Go\Punchout\Helper\Data $defaultHelper,
        \Punchout2Go\Punchout\Model\Transfer\QuoteDataHandlers\Shipping\AddressMapper $addressMapper
    ) {
        $this->helper = $transferHelper;
        $this->defaultHelper = $defaultHelper;
        $this->addressMapper = $addressMapper;
    }

    /**
     * @param \Magento\Quote\Api\Data\CartInterface $cart
     * @return mixed[]
     */
    public function handle(\Magento\Quote\Api\Data\CartInterface $cart): array
    {
        if (!$this->helper->isIncludeShipping($cart->getStoreId())/* || !$this->defaultHelper->isAddressToCart()*/) {
            return [];
        }
        $totals = $cart->getTotals();
        if (!isset($totals['shipping'])) {
            return [];
        }
        $shippingAddress = $cart->getShippingAddress();
        return [
             'shipping' => (string) $shippingAddress->getShippingAmount(),
             'shipping_method' => $shippingAddress->getShippingDescription(),
             'shipping_code' => $shippingAddress->getShippingMethod(),
             'addresses' => $this->getAddresses($cart->getAllAddresses())
         ];
    }

    /**
     * @param array $addresses
     * @return mixed[]
     */
    protected function getAddresses(array $addresses): array
    {
        $result = [];
        foreach ($addresses as $address) {
            $result[] = $this->addressMapper->getAddressData($address);
        }
        return $result;
    }
}
