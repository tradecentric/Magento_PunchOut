<?php
declare(strict_types=1);

namespace Punchout2Go\Punchout\Model\Transfer\QuoteDataHandlers\Items;

use Magento\Catalog\Api\Data\ProductCustomOptionInterface;
use Magento\Catalog\Model\Product\Configuration\Item\Option\OptionInterface;

/**
 * Class OptionsContainer
 * @package Punchout2Go\Punchout\Model\Transfer\QuoteDataHandlers\Items
 */
class OptionsContainer implements OptionsContainerInterface
{
    /**
     * @var OptionInterface
     */
    protected $itemOption;

    /**
     * @var ProductCustomOptionInterface
     */
    protected $productOption;

    /**
     * OptionsContainer constructor.
     * @param OptionInterface $itemOption
     * @param ProductCustomOptionInterface $productOption
     */
    public function __construct(OptionInterface $itemOption, ProductCustomOptionInterface $productOption)
    {
        $this->itemOption = $itemOption;
        $this->productOption = $productOption;
    }

    /**
     * @return OptionInterface
     */
    public function getItemOption(): OptionInterface
    {
        return $this->itemOption;
    }

    /**
     * @return ProductCustomOptionInterface
     */
    public function getProductOption(): ProductCustomOptionInterface
    {
        return $this->productOption;
    }
}
