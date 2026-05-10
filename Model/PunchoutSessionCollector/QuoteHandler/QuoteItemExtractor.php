<?php
declare(strict_types=1);

namespace Punchout2Go\Punchout\Model\PunchoutSessionCollector\QuoteHandler;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;

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
     * @var CartRepositoryInterface
     */
    protected $cartRepository;

    /**
     * QuoteItemExtractor constructor.
     * @param CartRepositoryInterface $cartRepository
     */
    public function __construct(CartRepositoryInterface $cartRepository)
    {
        $this->cartRepository = $cartRepository;
    }

    /**
     * @param $quoteId
     * @param $itemId
     * @return \Magento\Quote\Api\Data\CartItemInterface|null
     */
    public function getQuoteItem($quoteId, $itemId): ?\Magento\Quote\Api\Data\CartItemInterface
    {
        if (isset($this->items[$quoteId][$itemId])) {
            return $this->items[$quoteId][$itemId];
        }
        try {
            $quote = $this->cartRepository->get((int) $quoteId);
        } catch (NoSuchEntityException $e) {
            return null;
        }
        
        // Read items via getAllVisibleItems() / getItemsCollection() rather than getItems():
        // QuoteRepository\LoadHandler::load() short-circuits for inactive quotes and never
        // populates the KEY_ITEMS data key, so getItems() returns null. The collection-based
        // accessors hit the items table directly and work regardless of active state.
        $allItems = $quote instanceof Quote ? $quote->getAllVisibleItems() : (array) $quote->getItems();
        foreach ($allItems as $item) {
            $this->items[$quoteId][$item->getItemId()] = $item;
        }
        return $this->items[$quoteId][$itemId] ?? null;
    }
}
