<?php
declare(strict_types=1);

namespace Punchout2Go\Punchout\Model\Transfer\QuoteDataHandlers;

use Punchout2Go\Punchout\Model\Transfer\QuoteDataHandlerInterface;

/**
 * Class Discount
 * @package Punchout2Go\Punchout\Model\Transfer\QuoteDataHandlers
 */
class Discount implements QuoteDataHandlerInterface
{
    /**
     * @var \Punchout2Go\Punchout\Helper\Transfer
     */
    protected $helper;

    /**
     * Discount constructor.
     * @param \Punchout2Go\Punchout\Helper\Transfer $transferHelper
     */
    public function __construct(\Punchout2Go\Punchout\Helper\Transfer $transferHelper)
    {
        $this->helper = $transferHelper;
    }

    /**
     * @param \Magento\Quote\Api\Data\CartInterface $cart
     * @return mixed[]
     */
    public function handle(\Magento\Quote\Api\Data\CartInterface $cart): array
    {
        $result = [];
        if (!$this->helper->isIncludeDiscount()) {
            return $result;
        }

        $address = $cart->getIsVirtual() ? $cart->getBillingAddress() : $cart->getShippingAddress();
        $totals = $address->getTotals();
        if (!isset($totals['discount'])) {
            return $result;
        }

        $total = $totals['discount'];
        return [
            'discount' => $total->getValue(),
            'discount_title' => (string) $total->getTitle()
        ];
    }
}
