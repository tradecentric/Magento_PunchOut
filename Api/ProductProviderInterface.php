<?php
declare(strict_types=1);

namespace Punchout2Go\Punchout\Api;

use Magento\Catalog\Model\Product;
use Magento\Quote\Model\Quote\Item;

/**
 * Interface LoggerInterface
 * @package Punchout2Go\Punchout\Api
 */
interface ProductProviderInterface
{
    /**
     * Get product from quote item
     * 
     * @param Item $item
     * 
     * @return Product
     */
    public function getProductFromQuoteItem(Item $item): Product;
}
