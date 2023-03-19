<?php
declare(strict_types=1);

namespace Punchout2Go\Punchout\Model\Transfer\QuoteItemTransferData\Fields;

use Magento\Catalog\Api\Data\ProductInterface;
use Punchout2Go\Punchout\Model\Transfer\QuoteItemTransferData\ProductRelatedDataHandlerInterface;
use Magento\Weee\Helper\Data;

/**
 * FixedProductTax
 * Class FixedProductTax
 * @package Punchout2Go\Punchout\Model\Transfer\QuoteItemTransferData
 */
class FixedProductTax implements ProductRelatedDataHandlerInterface
{
    /**
     * @var Data
     */
    private $helper;

    /**
     * @param Data $helper
     */
    public function __construct(Data $helper)
    {
        $this->helper = $helper;
    }

    /**
     * @param ProductInterface $product
     * @param null $storeId
     * @return array|string
     */
    public function handle(ProductInterface $product, $storeId = null): array
    {
        $result = [];
        foreach ($this->helper->getProductWeeeAttributes($product) as $item) {
            $result[$item->getCode()] = $product->getData($item->getCode());
        }
        return $result;
    }
}
