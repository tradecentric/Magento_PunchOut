<?php
declare(strict_types=1);

namespace Punchout2Go\Punchout\Model\Transfer\QuoteDataHandlers\Items;

use Magento\Framework\Exception\LocalizedException;

/**
 * Class ItemTypeFactory
 * @package Punchout2Go\Punchout\Model\Transfer\QuoteDataHandlers\Items
 */
class ItemTypeResolver
{
    const DEFAULT = 'default';
    const WITH_OPTIONS = 'with_options';

    /**
     * @var \Punchout2Go\Punchout\Helper\Transfer
     */
    protected $transferHelper;

    /**
     * @var ItemOptionsResolver
     */
    protected $itemOptionsResolver;

    /**
     * ItemTypeResolver constructor.
     * @param \Punchout2Go\Punchout\Helper\Transfer $helper
     * @param ItemOptionsResolver $itemOptionsResolver
     */
    public function __construct(
        \Punchout2Go\Punchout\Helper\Transfer $helper,
        ItemOptionsResolver $itemOptionsResolver
    ) {
        $this->transferHelper = $helper;
        $this->itemOptionsResolver = $itemOptionsResolver;
    }

    /**
     * @param \Magento\Quote\Api\Data\CartItemInterface $cartItem
     * @return string
     * @throws LocalizedException
     */
    public function resolve(\Magento\Quote\Api\Data\CartItemInterface $cartItem): string
    {
        $type = static::DEFAULT;
        if (!$this->transferHelper->isSplitOptionSkus()) {
            return $type;
        }
        if ($this->itemOptionsResolver->resolve($cartItem)) {
            $type = static::WITH_OPTIONS;
        }
        return $type;
    }
}
