<?php
declare(strict_types=1);

namespace Punchout2Go\Punchout\Model\Transfer\CustomFields\Parts;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Quote\Api\Data\CartInterface;
use Punchout2Go\Punchout\Model\Transfer\CustomFields\QuotePartInterface;


/**
 * Class QuoteStore
 * @package Punchout2Go\Punchout\Model\Transfer\CustomFields\Parts
 */
class QuoteStore implements QuotePartInterface
{
    /**
     * @param CartInterface $cart
     * @param string $path
     * @return string
     */
    public function handle(CartInterface $cart, string $path): string
    {
        $store = $cart->getStore();
        return $store->getData($path);
    }
}
