<?php
declare(strict_types=1);

namespace Punchout2Go\Punchout\Model\Transfer\QuoteItemTransferData\Fields;

use Magento\Catalog\Api\Data\ProductInterface;
use Punchout2Go\Punchout\Model\Transfer\QuoteItemTransferData\ProductRelatedDataHandlerInterface;

/**
 * classification
 * Class Classification
 * @package Punchout2Go\Punchout\Model\Transfer\QuoteItemTransferData
 */
class Classification implements ProductRelatedDataHandlerInterface
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
     * @return mixed[]|string
     */
    public function handle(ProductInterface $product, ?$storeId = null): array
    {
        $classificationField = $this->helper->getProductClassificationField($storeId);
        $value = $product->getData($classificationField) ?: $this->helper->getProductDefaultClassification($storeId);
        return ['classification' => $value];
    }
}
