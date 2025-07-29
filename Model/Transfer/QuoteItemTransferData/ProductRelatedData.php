<?php
declare(strict_types=1);

namespace Punchout2Go\Punchout\Model\Transfer\QuoteItemTransferData;

/**
 * Class ProductRelatedData
 * @package Punchout2Go\Punchout\Model\Transfer\QuoteItemTransferData
 */
class ProductRelatedData implements ProductRelatedDataHandlerInterface
{
    /**
     * @var array
     */
    protected $mapping = [];

    /**
     * QuoteRelatedData constructor.
     * @param array $mapping
     */
    public function __construct(array $mapping = [])
    {
        $this->mapping = $mapping;
    }

    /**
     * map from product
     *
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @param null $storeId
     * @return mixed[]
     */
    public function handle(\Magento\Catalog\Api\Data\ProductInterface $product, ?$storeId = null): array
    {
        $result = [];
        foreach ($this->mapping as $fieldCode => $field) {
            if (is_object($field) && ($field instanceof ProductRelatedDataHandlerInterface)) {
                $result = array_merge($result, $field->handle($product, $storeId));
                continue;
            }
            $result[$fieldCode] = $product->getData($field);
        }
        return $result;
    }
}
