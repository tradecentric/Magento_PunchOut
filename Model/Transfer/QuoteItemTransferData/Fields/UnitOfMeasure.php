<?php
declare(strict_types=1);

namespace Punchout2Go\Punchout\Model\Transfer\QuoteItemTransferData\Fields;

use Magento\Catalog\Api\Data\ProductInterface;
use Punchout2Go\Punchout\Model\Transfer\QuoteItemTransferData\ProductRelatedDataHandlerInterface;

/**
 *
 *
 * Class Classification
 * @package Punchout2Go\Punchout\Model\Transfer\QuoteItemTransferData
 */
class UnitOfMeasure implements ProductRelatedDataHandlerInterface
{
    /**
     * @var \Punchout2Go\Punchout\Helper\Data
     */
    protected $helper;

    /**
     * Classification constructor.
     * @param \Punchout2Go\Punchout\Helper\Transfer $helper
     */
    public function __construct(
        \Punchout2Go\Punchout\Helper\Transfer $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * @param ProductInterface $product
     * @param null $storeId
     * @return array
     */
    public function handle(ProductInterface $product, $storeId = null): array
    {
        $uom_field = $this->helper->getUomField($storeId);
        $uom = $product->getData($uom_field) ?: $this->helper->getUomDefault($storeId);
        return ['uom' => $uom];
    }
}
