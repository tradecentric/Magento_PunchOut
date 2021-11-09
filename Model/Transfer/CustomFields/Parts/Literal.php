<?php
declare(strict_types=1);

namespace Punchout2Go\Punchout\Model\Transfer\CustomFields\Parts;

use Magento\Catalog\Api\Data\ProductInterface;
use Punchout2Go\Punchout\Model\Transfer\CustomFields\ProductPartInterface;

/**
 * Class Literal
 * @package Punchout2Go\Punchout\Model\Transfer\CustomFields\Parts
 */
class Literal implements ProductPartInterface
{
    /**
     * @param \Magento\Catalog\Model\AbstractModel $model
     * @param string $path
     * @return string
     */
    public function handle(ProductInterface $model, string $path): string
    {
        return $path;
    }
}
