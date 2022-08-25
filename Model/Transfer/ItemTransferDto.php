<?php
declare(strict_types=1);

namespace Punchout2Go\Punchout\Model\Transfer;

use Magento\Quote\Api\Data\CartItemInterface;
use Punchout2Go\Punchout\Api\Data\ItemTransferDtoInterface;

/**
 * Class ItemTransferDto
 * @package Punchout2Go\Punchout\Model\Transfer
 */
class ItemTransferDto implements ItemTransferDtoInterface
{
    /**
     * @var CartInterface
     */
    protected $item;

    /**
     * @var
     */
    protected $storeId;

    /**
     * ItemTransferDto constructor.
     * @param CartItemInterface $item
     * @param $storeId
     */
    public function __construct(CartItemInterface $item, $storeId)
    {
        $this->item = $item;
        $this->storeId = $storeId;
    }

    /**
     * @return int
     */
    public function getStoreId(): int
    {
        return (int) $this->storeId;
    }

    /**
     * @return CartItemInterface
     */
    public function getItem(): CartItemInterface
    {
        return $this->item;
    }
}
