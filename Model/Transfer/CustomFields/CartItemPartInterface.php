<?php
declare(strict_types=1);

namespace Punchout2Go\Punchout\Model\Transfer\CustomFields;

use Magento\Quote\Api\Data\CartItemInterface;

/**
 * Interface CartItemPartInterface
 * @package Punchout2Go\Punchout\Model\Transfer\CustomFields
 */
interface CartItemPartInterface
{
    /**
     * @param \Magento\Quote\Api\Data\CartItemInterface $model
     * @param string $path
     * @return string
     */
    public function handle(CartItemInterface $product, string $path): string;
}
