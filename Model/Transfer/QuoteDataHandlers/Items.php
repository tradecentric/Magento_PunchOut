<?php
declare(strict_types=1);

namespace Punchout2Go\Punchout\Model\Transfer\QuoteDataHandlers;

use Punchout2Go\Punchout\Model\Transfer\QuoteDataHandlerInterface;

/**
 * Class Items
 * @package Punchout2Go\Punchout\Model\Transfer\QuoteDataHandlers
 */
class Items implements QuoteDataHandlerInterface
{

    /**
     * @var \Punchout2Go\Punchout\Model\Transfer\QuoteItemTransferDataFactory
     */
    protected $itemTypeFactory;

    /**
     * @var \Punchout2Go\Punchout\Helper\Transfer
     */
    protected $itemTypeResolver;

    /**
     * Items constructor.
     * @param Items\ItemTypeFactory $itemTypeFactory
     * @param Items\ItemTypeResolver $itemTypeResolver
     */
    public function __construct(
        Items\ItemTypeFactory $itemTypeFactory,
        Items\ItemTypeResolver $itemTypeResolver
    ) {
        $this->itemTypeFactory = $itemTypeFactory;
        $this->itemTypeResolver = $itemTypeResolver;
    }

    /**
     * @param \Magento\Quote\Api\Data\CartInterface $cart
     * @return array
     */
    public function handle(\Magento\Quote\Api\Data\CartInterface $cart): array
    {
        $result = [];
        foreach ($cart->getAllVisibleItems() as $item) {
            $itemType = $this->itemTypeFactory->create($this->itemTypeResolver->resolve($item), $item);
            $result = array_merge($result, $itemType->getData($item, $cart->getStoreId()));
        }
        return $result;
    }
}
