<?php
declare(strict_types=1);

namespace Punchout2Go\Punchout\Model\Transfer\QuoteItemTransferData;

use Magento\Quote\Api\Data\CartItemInterface;

/**
 * Interface QuoteItemDataHandlerInterface
 * @package Punchout2Go\Punchout\Model\Transfer\QuoteDataHandlers\Items
 */
interface QuoteItemRelatedDataHandlerInterface
{
    /**
     * @param CartItemInterface $cartItem
     * @param $storeId
     * @return array
     */
    public function handle(CartItemInterface $cartItem, $storeId): array;
}
