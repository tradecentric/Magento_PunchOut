<?php
declare(strict_types=1);

namespace Punchout2Go\Punchout\Api;

use Magento\Quote\Api\Data\CartItemInterface;
use Punchout2Go\Punchout\Api\Data\ItemTransferDtoInterface;

/**
 * Interface PunchoutTransferDataInterface
 * @package Punchout2Go\Punchout\Api
 */
interface TransferCartItemDataInterface
{
    /**
     * @param ItemTransferDtoInterface $dto
     * @return bool
     */
    public function supports(ItemTransferDtoInterface $dto): bool;

    /**
     * @param ItemTransferDtoInterface $dto
     * @return mixed[]
     */
    public function getData(ItemTransferDtoInterface $dto): array;
}
