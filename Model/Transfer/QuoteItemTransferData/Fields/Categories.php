<?php
declare(strict_types=1);

namespace Punchout2Go\Punchout\Model\Transfer\QuoteItemTransferData\Fields;

use Magento\Catalog\Api\Data\ProductInterface;
use Punchout2Go\Punchout\Model\Transfer\QuoteItemTransferData\ProductRelatedDataHandlerInterface;

/**
 * categories
 *
 * Class Categories
 * @package Punchout2Go\Punchout\Model\Transfer\QuoteItemTransferData
 */
class Categories implements ProductRelatedDataHandlerInterface
{
    /**
     * @param ProductInterface $product
     * @param null $storeId
     * @return mixed[]|string
     */
    public function handle(ProductInterface $product, $storeId = null): array
    {
        $cats = $product->getCategoryCollection()->addAttributeToSelect(
            'path'
        )->setStoreId(
            $storeId
        );
        $result = array_map(function ($cat) {
            return $cat['path'];
        }, $cats->getData());
        return ['categories' =>  ":" . implode(":", $result) . ":"];
    }
}
