<?php
declare(strict_types=1);

namespace Punchout2Go\Punchout\Model\Transfer\QuoteItemTransferData\Fields;

use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Quote\Api\Data\CartItemInterface;
use Punchout2Go\Punchout\Model\Transfer\QuoteItemTransferData\QuoteItemRelatedDataHandlerInterface;

/**
 * Currency
 *
 * Class Currency
 * @package Punchout2Go\Punchout\Model\Transfer\QuoteItemTransferData
 */
class Currency implements QuoteItemRelatedDataHandlerInterface
{
    /**
     * @param CartItemInterface $product
     * @param $storeId
     * @return mixed[]
     */
    public function handle(CartItemInterface $product, $storeId): array
    {
        $store = $product->getQuote()->getStore();
        return ['currency' => $store->getCurrentCurrencyCode()];
    }
}
