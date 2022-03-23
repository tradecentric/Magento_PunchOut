<?php
declare(strict_types=1);

namespace Punchout2Go\Punchout\Model\Transfer\CustomFields\Parts;

use Magento\Catalog\Api\Data\ProductInterface;
use Punchout2Go\Punchout\Model\Transfer\CustomFields\ProductPartInterface;

/**
 * Class Product
 * @package Punchout2Go\Punchout\Model\Transfer\CustomFields\Parts
 */
class Product implements ProductPartInterface
{
    /**
     * @param ProductInterface $product
     * @param string $path
     * @return string
     */
    public function handle(ProductInterface $product, string $path): string
    {
        $returnValue = '';
        if ($product->getData($path)) {
            $attribute = $product->getResource()->getAttribute($path);
            $returnValue = $attribute->getFrontend()->getValue($product);
        }
        return $returnValue;
    }
}
