<?php
declare(strict_types=1);

namespace Punchout2Go\Punchout\Model\Transfer\QuoteDataHandlers\Items;

use Magento\Catalog\Api\Data\ProductCustomOptionInterface;
use Magento\Catalog\Model\Product\Configuration\Item\Option\OptionInterface;

/**
 * Interface OptionsContainerInterface
 * @package Punchout2Go\Punchout\Model\Transfer\QuoteDataHandlers\Items
 */
interface OptionsContainerInterface
{
    /**
     * @return ProductCustomOptionInterface
     */
    public function getProductOption(): ProductCustomOptionInterface;

    /**
     * @return OptionInterface
     */
    public function getItemOption(): OptionInterface;
}
