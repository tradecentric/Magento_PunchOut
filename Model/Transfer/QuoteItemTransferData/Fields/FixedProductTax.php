<?php
declare(strict_types=1);

namespace Punchout2Go\Punchout\Model\Transfer\QuoteItemTransferData\Fields;

use Magento\Catalog\Api\Data\ProductInterface;
use Punchout2Go\Punchout\Model\Transfer\QuoteItemTransferData\ProductRelatedDataHandlerInterface;

/**
 * FixedProductTax
 * Class FixedProductTax
 * @package Punchout2Go\Punchout\Model\Transfer\QuoteItemTransferData
 */
class FixedProductTax implements ProductRelatedDataHandlerInterface
{
    /**
     * @param ProductInterface $product
     * @param null $storeId
     * @return array|string
     */
    public function handle(ProductInterface $product, $storeId = null): array
    {
        if ($product->getFixedProductTax()) {
            return ['fixed_product_tax' => $product->getFixedProductTax()];
        }
        return [];
    }
}
