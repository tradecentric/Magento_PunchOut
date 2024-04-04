<?php
declare(strict_types=1);

namespace Punchout2Go\Punchout\Model\Transfer\QuoteDataHandlers;

use Punchout2Go\Punchout\Model\Transfer\QuoteDataHandlerInterface;

/**
 * Class Tax
 * @package Punchout2Go\Punchout\Model\Transfer\QuoteDataHandlers
 */
class Tax implements QuoteDataHandlerInterface
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
        if (!$this->helper->isIncludeTax()) {
            return [];
        }
        $totals = $cart->getTotals();
        if (!isset($totals['tax'])) {
            return [];
        }
        $total = $totals['tax'];
        return [
            'tax'  => (float) $total->getValue(),
            'tax_description' => (string) $total->getTitle()
        ];
    }
}
