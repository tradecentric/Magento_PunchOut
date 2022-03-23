<?php
declare(strict_types=1);

namespace Punchout2Go\Punchout\Model\Transfer\QuoteDataHandlers\Items;

use Magento\Framework\Exception\LocalizedException;
use Punchout2Go\Punchout\Api\TransferCartItemDataInterface;

/**
 * Class ItemTypeFactory
 * @package Punchout2Go\Punchout\Model\Transfer\QuoteDataHandlers\Items
 */
class ItemTypeFactory
{
    /**
     * @var array
     */
    protected $resolvers = [];

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**ram array $resolvers
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        array $resolvers = []
    ) {
        $this->resolvers = $resolvers;
        $this->objectManager = $objectManager;
    }

    /**
     * @param \Magento\Quote\Api\Data\CartItemInterface $cartItem
     */
    public function create(string $itemType, \Magento\Quote\Api\Data\CartItemInterface $cartItem) : TransferCartItemDataInterface
    {
        if (!isset($this->resolvers[$itemType])) {
            throw new LocalizedException(__('Item Type Resolver doesnt exist'));
        }

        return $this->objectManager->create($this->resolvers[$itemType]);
    }
}
