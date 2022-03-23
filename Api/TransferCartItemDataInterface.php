<?php
declare(strict_types=1);

namespace Punchout2Go\Punchout\Api;

use Magento\Quote\Api\Data\CartItemInterface;

/**
 * Interface PunchoutTransferDataInterface
 * @package Punchout2Go\Punchout\Api
 */
interface TransferCartItemDataInterface
{
    public function getData(CartItemInterface $cartItem, $storeId = null): array;
}
