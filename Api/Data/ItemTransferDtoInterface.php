<?php
declare(strict_types=1);

namespace Punchout2Go\Punchout\Api\Data;

use Magento\Quote\Api\Data\CartItemInterface;

/**
 * Interface ItemTransferDtoInterface
 * @package Punchout2Go\Punchout\Api\Data
 */
interface ItemTransferDtoInterface
{
    /**
     * @return int
     */
    public function getStoreId(): int;

    /**
     * @return CartItemInterface
     */
    public function getItem(): CartItemInterface;
}
