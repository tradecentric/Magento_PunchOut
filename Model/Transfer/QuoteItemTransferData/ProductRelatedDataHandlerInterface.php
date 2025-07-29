<?php
declare(strict_types=1);

namespace Punchout2Go\Punchout\Model\Transfer\QuoteItemTransferData;

use Magento\Catalog\Api\Data\ProductInterface;

/**
 * Interface QuoteItemDataHandlerInterface
 * @package Punchout2Go\Punchout\Model\Transfer\QuoteDataHandlers\Items
 */
interface ProductRelatedDataHandlerInterface
{
    /**
     * @param ProductInterface $product
     * @param null $storeId
     * @return mixed[]
     */
    public function handle(ProductInterface $product, ?$storeId = null): array;
}
