<?php
declare(strict_types=1);

namespace Punchout2Go\Punchout\Model\Transfer\QuoteItemTransferData\Fields;

use Magento\Quote\Api\Data\CartItemInterface;
use Punchout2Go\Punchout\Model\Transfer\QuoteItemTransferData\QuoteItemRelatedDataHandlerInterface;

/**
 *
 * Class Classification
 * @package Punchout2Go\Punchout\Model\Transfer\QuoteItemTransferData
 */
class SupplierAuxId implements QuoteItemRelatedDataHandlerInterface
{
    /**
     * @var \Punchout2Go\Punchout\Helper\Data
     */
    protected $helper;

    /**
     * SupplierId constructor.
     * @param \Punchout2Go\Punchout\Helper\Transfer $helper
     */
    public function __construct(
        \Punchout2Go\Punchout\Helper\Transfer $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * @param CartItemInterface $cartItem
     * @param $storeId
     * @return array
     */
    public function handle(CartItemInterface $cartItem, $storeId): array
    {
        return [
            'supplierauxid' => $this->helper->getLineId((int) $cartItem->getQuoteId(), (int) $cartItem->getItemId())
        ];
    }
}
