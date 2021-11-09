<?php
declare(strict_types=1);

namespace Punchout2Go\Punchout\Model\Transfer\QuoteItemTransferData\Fields;

use Magento\Catalog\Api\Data\ProductInterface;
use Punchout2Go\Punchout\Model\Transfer\QuoteItemTransferData\ProductRelatedDataHandlerInterface;

/**
 * Class ProductWithOptionsPriceField
 * @package Punchout2Go\Punchout\Model\Transfer\QuoteItemTransferData
 */
class ProductFinalPrice implements ProductRelatedDataHandlerInterface
{
    /**
     * @param ProductInterface $product
     * @param null $storeId
     * @return array
     */
    public function handle(ProductInterface $product, $storeId = null): array
    {
        return ['unitprice' => $product->getFinalPrice()];
    }
}
