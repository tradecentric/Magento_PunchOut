<?php
declare(strict_types=1);

namespace Punchout2Go\Punchout\Model\Transfer\QuoteDataHandlers;

use Punchout2Go\Punchout\Model\Transfer\QuoteDataHandlerInterface;

/**
 * Class Totals
 * @package Punchout2Go\Punchout\Model\Transfer\QuoteDataHandlers
 */
class Totals implements QuoteDataHandlerInterface
{
    /**
     * @param \Magento\Quote\Api\Data\CartInterface $cart
     * @return array
     */
    public function handle(\Magento\Quote\Api\Data\CartInterface $cart): array
    {
        $totals = $cart->getTotals();
        $result = ['type' => get_class($cart)];
        if (isset($totals['subtotal'])) {
            $total = $totals['subtotal'];
            $result['total'] = $total->getValue();
        }
        if (isset($totals['grand_total'])) {
            $total = $totals['grand_total'];
            $result['grand_total'] = $total->getValue();
        }

        $store = $cart->getStore();
        $result['currency'] = $store->getCurrentCurrencyCode();
        $result['currency_rate'] = $store->getCurrentCurrencyRate();
        return $result;
    }
}
