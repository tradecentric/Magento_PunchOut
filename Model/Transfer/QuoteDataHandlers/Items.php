<?php
declare(strict_types=1);

namespace Punchout2Go\Punchout\Model\Transfer\QuoteDataHandlers;

use Punchout2Go\Punchout\Model\Transfer\QuoteDataHandlerInterface;
use Punchout2Go\Punchout\Api\Data\ItemTransferDtoInterfaceFactory;

/**
 * Class Items
 * @package Punchout2Go\Punchout\Model\Transfer\QuoteDataHandlers
 */
class Items implements QuoteDataHandlerInterface
{
    /**
     * @var \Punchout2Go\Punchout\Model\Transfer\QuoteItemTransferDataFactory
     */
    protected $itemTypePool;

    /**
     * @var ItemTransferDtoInterfaceFactory
     */
    protected $dtoInterfaceFactory;

    /**
     * Items constructor.
     * @param Items\ItemTypePool $itemTypePool
     * @param ItemTransferDtoInterfaceFactory $dtoInterfaceFactory
     */
    public function __construct(
        Items\ItemTypePool $itemTypePool,
        ItemTransferDtoInterfaceFactory $dtoInterfaceFactory
    ) {
        $this->itemTypePool = $itemTypePool;
        $this->dtoInterfaceFactory = $dtoInterfaceFactory;
    }

    /**
     * @param \Magento\Quote\Api\Data\CartInterface $cart
     * @return mixed[]
     */
    public function handle(\Magento\Quote\Api\Data\CartInterface $cart): array
    {
        $result = [];
        foreach ($cart->getAllVisibleItems() as $item) {
            $dto = $this->dtoInterfaceFactory->create(['item' => $item, 'storeId' => $cart->getStoreId()]);
            $itemType = $this->itemTypePool->get($dto);
            $result = array_merge($result, $itemType->getData($dto));
        }
        return $result;
    }
}
