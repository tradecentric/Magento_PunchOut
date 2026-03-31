<?php
declare(strict_types=1);

namespace Punchout2Go\Punchout\Model\PunchoutSessionCollector\QuoteHandler;

use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Quote\Model\ResourceModel\Quote\Item\CollectionFactory;

/**
 * Class QuoteItemExtractor
 * @package Punchout2Go\Punchout\Model\QuoteHandler
 */
class QuoteItemExtractor
{
    /**
     * @var array
     */
    protected $items = [];

    /**
     * @var CollectionFactory
     */
    protected $itemCollectionFactory;

    /**
     * QuoteItemExtractor constructor.
     * @param CollectionFactory $itemCollectionFactory
     */
    public function __construct(
        CollectionFactory $itemCollectionFactory
    ) {
        $this->itemCollectionFactory = $itemCollectionFactory;
    }

    /**
     * @param $quoteId
     * @param $itemId
     * @return CartItemInterface|null
     */
    public function getQuoteItem($quoteId, $itemId): ?CartItemInterface
    {
        if (isset($this->items[$quoteId][$itemId])) {
            return $this->items[$quoteId][$itemId];
        }
        $collection = $this->itemCollectionFactory->create();
        $collection->addFieldToFilter('quote_id', (int) $quoteId);
        foreach ($collection as $item) {
            $this->items[$quoteId][$item->getItemId()] = $item;
        }
        return $this->items[$quoteId][$itemId] ?? null;
    }
}
