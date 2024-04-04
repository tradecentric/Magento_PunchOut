<?php
declare(strict_types=1);

namespace Punchout2Go\Punchout\Model\Transfer\QuoteItemTransferData\Fields;

use Magento\Quote\Api\Data\CartItemInterface;
use Punchout2Go\Punchout\Model\Transfer\QuoteItemTransferData\QuoteItemRelatedDataHandlerInterface;

/**
 * Currency
 *
 * Class Currency
 * @package Punchout2Go\Punchout\Model\Transfer\QuoteItemTransferData
 */
class UnitPrice implements QuoteItemRelatedDataHandlerInterface
{
    /**
     * @param CartItemInterface $product
     * @param $storeId
     * @return mixed[]
     */
    public function handle(CartItemInterface $product, $storeId): array
    {
        return ['unitprice' => $product->getPriceInclTax() + $product->getWeeeTaxAppliedAmount()];
    }
}
