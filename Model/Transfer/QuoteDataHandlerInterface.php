<?php
declare(strict_types=1);

namespace Punchout2Go\Punchout\Model\Transfer;

/**
 * Interface QuoteDataHandlerInterface
 * @package Punchout2Go\Punchout\Model\Transfer
 */
interface QuoteDataHandlerInterface
{
    /**
     * @param \Magento\Quote\Api\Data\CartInterface $cart
     * @return mixed[]
     */
    public function handle(\Magento\Quote\Api\Data\CartInterface $cart): array;
}
