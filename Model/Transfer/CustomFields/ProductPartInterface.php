<?php
declare(strict_types=1);

namespace Punchout2Go\Punchout\Model\Transfer\CustomFields;

use Magento\Catalog\Api\Data\ProductInterface;

/**
 * Interface ProductPartInterface
 * @package Punchout2Go\Punchout\Model\Transfer\CustomFields
 */
interface ProductPartInterface
{
    /**
     * @param \Magento\Catalog\Model\AbstractModel $model
     * @param string $path
     * @return string
     */
    public function handle(ProductInterface $product, string $path): string;
}
