<?php
declare(strict_types=1);

namespace Punchout2Go\Punchout\Model\Transfer\CustomFields;

use Magento\Quote\Api\Data\CartInterface;

/**
 * Interface ProductPartInterface
 * @package Punchout2Go\Punchout\Model\Transfer\CustomFields
 */
interface QuotePartInterface
{
    /**
     * @param \Magento\Catalog\Model\AbstractModel $model
     * @param string $path
     * @return string
     */
    public function handle(CartInterface $cart, string $path): string;
}
