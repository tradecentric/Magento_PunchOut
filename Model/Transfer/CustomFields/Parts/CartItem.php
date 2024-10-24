<?php
declare(strict_types=1);

namespace Punchout2Go\Punchout\Model\Transfer\CustomFields\Parts;

use Magento\Catalog\Api\Data\CartItemInterface;
use Punchout2Go\Punchout\Model\Transfer\CustomFields\CartItemPartInterface;

/**
 * Class CartItem
 * @package Punchout2Go\Punchout\Model\Transfer\CustomFields\Parts
 */
class CartItem implements ProductPartInterface
{
    /**
     * @param CartItemInterface $product
     * @param string $path
     * @return string
     */
    public function handle(CartItemInterface $product, string $path): string
    {
        $returnValue = '';
        if ($product->getData($path)) {
            $attribute = $product->getResource()->getAttribute($path);
            $returnValue = $attribute->getFrontend()->getValue($product);
        }
        return $returnValue;
    }
}
