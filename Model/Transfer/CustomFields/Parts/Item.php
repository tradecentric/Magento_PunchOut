<?php
declare(strict_types=1);

namespace Punchout2Go\Punchout\Model\Transfer\CustomFields\Parts;

use Magento\Quote\Api\Data\CartItemInterface;
use Punchout2Go\Punchout\Model\Transfer\CustomFields\CartItemPartInterface;

/**
 * Class Item
 * @package Punchout2Go\Punchout\Model\Transfer\CustomFields\Parts
 */
class Item implements CartItemPartInterface
{
    /**
     * @param CartItemInterface $product
     * @param string $path
     * @return string
     */
    public function handle(CartItemInterface $product, string $path): string
    {
        $returnValue = '';
<<<<<<< HEAD
        $productData = $product->getData($path);
        if (isset($productData)) {
           $returnValue = $product->getData($path);
=======
		$productData = $product->getData($path);
        if (isset($productData)) {
//            $attribute = $product->getResource()->getAttribute($path);
//            $returnValue = $attribute->getFrontend()->getValue($product);
			$returnValue = $product->getData($path);
>>>>>>> b7376b6 (CN-393 item mapping initial)
        }
        return $returnValue;
    }
}
