<?php
declare(strict_types=1);

namespace Punchout2Go\Punchout\Model\Transfer\QuoteDataHandlers\Items;

use Punchout2Go\Punchout\Model\Transfer\QuoteDataHandlers\Items\OptionsContainerInterfaceFactory;

/**
 * Class ItemOptionsResolver
 * @package Punchout2Go\Punchout\Model\Transfer\QuoteDataHandlers\Items
 */
class ItemOptionsResolver
{
    /**
     * @var \Punchout2Go\Punchout\Model\Transfer\QuoteDataHandlers\Items\OptionsContainerInterfaceFactory
     */
    protected $containerFactory;

    /**
     * ItemOptionsResolver constructor.
     * @param OptionsContainerInterfaceFactory $containerFactory
     */
    public function __construct(OptionsContainerInterfaceFactory $containerFactory)
    {
        $this->containerFactory = $containerFactory;
    }

    /**
     * @param \Magento\Catalog\Model\Product\Configuration\Item\ItemInterface $item
     * @return OptionsContainerInterface[]
     */
    public function resolve(\Magento\Catalog\Model\Product\Configuration\Item\ItemInterface $item)
    {
        $product = $item->getProduct();
        $options = [];
        $optionIds = $item->getOptionByCode('option_ids');
        if ($optionIds) {
            foreach (explode(',', $optionIds->getValue()) as $optionId) {
                $option = $product->getOptionById($optionId);
                if ($option) {
                    $itemOption = $item->getOptionByCode('option_' . $option->getId());
                    $options[] = $this->containerFactory->create([
                        'productOption' => $option,
                        'itemOption' => $itemOption
                    ]);
                }
            }
        }
        return $options;
    }
}
