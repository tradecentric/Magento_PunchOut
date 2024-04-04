<?php
declare(strict_types=1);

namespace Punchout2Go\Punchout\Model\Transfer\QuoteItemTransferData\Fields;

use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Quote\Api\Data\CartItemInterface;
use Punchout2Go\Punchout\Model\Transfer\QuoteItemTransferData\QuoteItemRelatedDataHandlerInterface;
use Punchout2Go\Punchout\Helper\ProductConfiguration;

/**
 * categories
 *
 * Class Categories
 * @package Punchout2Go\Punchout\Model\Transfer\QuoteItemTransferData
 */
class Configuration implements QuoteItemRelatedDataHandlerInterface
{
    /**
     * @var ProductConfiguration
     */
    protected $configurable;

    /**
     * @param ProductConfiguration $configurable
     */
    public function __construct(ProductConfiguration $configurable)
    {
        $this->configurable = $configurable;
    }

    /**
     * @param CartItemInterface $product
     * @param $storeId
     * @return mixed[]
     */
    public function handle(CartItemInterface $product, $storeId): array
    {
        if ($product->getProductType() !== Configurable::TYPE_CODE) {
            return [];
        }
        $result = [];

        foreach ($this->configurable->getOptions($product) as $option) {
            $result[$option['attribute_code']] = $option['value'];
        }
        return $result;
    }
}
