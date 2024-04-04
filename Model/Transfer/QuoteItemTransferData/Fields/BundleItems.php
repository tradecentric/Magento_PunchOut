<?php
declare(strict_types=1);

namespace Punchout2Go\Punchout\Model\Transfer\QuoteItemTransferData\Fields;

use Magento\Bundle\Model\Product\Type;
use Magento\Quote\Api\Data\CartItemInterface;
use Punchout2Go\Punchout\Model\Transfer\QuoteItemTransferData\QuoteItemRelatedDataHandlerInterface;
use Punchout2Go\Punchout\Model\Transfer\QuoteDataHandlers\Items\ItemTypePool;
use Punchout2Go\Punchout\Api\Data\ItemTransferDtoInterfaceFactory;

/**
 * Class BundleItems
 * @package Punchout2Go\Punchout\Model\Transfer\QuoteItemTransferData\Fields
 */
class BundleItems implements QuoteItemRelatedDataHandlerInterface
{
    /**
     * @var ItemTypePool
     */
    protected $itemTypePool;

    /**
     * @var ItemTypeResolver
     */
    protected $itemTypeResolver;

    /**
     * @var ItemTransferDtoInterfaceFactory
     */
    protected $dtoInterfaceFactory;

    /**
     * BundleItems constructor.
     * @param ItemTypePool $itemTypePool
     * @param ItemTransferDtoInterfaceFactory $dtoInterfaceFactory
     */
    public function __construct(ItemTypePool $itemTypePool, ItemTransferDtoInterfaceFactory $dtoInterfaceFactory)
    {
        $this->itemTypePool = $itemTypePool;
        $this->dtoInterfaceFactory = $dtoInterfaceFactory;
    }

    /**
     * @param CartItemInterface $cartItem
     * @param $storeId
     * @return mixed[]
     * @throws \Magento\Framework\Exception\LocalizedException
     */
   public function handle(CartItemInterface $cartItem, $storeId): array
   {
       if ($cartItem->getProductType() !== Type::TYPE_CODE) {
           return [];
       }
       $result = [];
       foreach ($cartItem->getChildren() as $child) {
           $dto = $this->dtoInterfaceFactory->create(['item' => $child, 'storeId' => $storeId]);
           $itemType = $this->itemTypePool->get($dto);
           $result = array_merge($result, $itemType->getData($dto));
       }
       return ['child_items' => $result];
   }
}
