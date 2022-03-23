<?php
declare(strict_types=1);

namespace Punchout2Go\Punchout\Model\Transfer\QuoteDataHandlers\Shipping;

/**
 * Interface AddressFieldInterface
 * @package Punchout2Go\Punchout\Model\Transfer\QuoteDataHandlers\Shipping
 */
interface AddressFieldInterface
{
    /**
     * @param \Magento\Quote\Api\Data\AddressInterface $address
     * @return mixed
     */
    public function handle(\Magento\Quote\Api\Data\AddressInterface $address);
}
