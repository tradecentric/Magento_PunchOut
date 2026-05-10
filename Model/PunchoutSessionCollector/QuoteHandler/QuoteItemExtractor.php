<?php
declare(strict_types=1);

namespace Punchout2Go\Punchout\Model\PunchoutSessionCollector\QuoteHandler;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CartRepositoryInterface;

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
        foreach ((array) $quote->getItems() as $item) {
            $this->items[$quoteId][$item->getItemId()] = $item;
        }
        return $this->items[$quoteId][$itemId] ?? null;
    }
}
