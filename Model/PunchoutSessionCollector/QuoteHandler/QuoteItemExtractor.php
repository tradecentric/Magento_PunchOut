<?php
declare(strict_types=1);

namespace Punchout2Go\Punchout\Model\PunchoutSessionCollector\QuoteHandler;

use Magento\Framework\Exception\NoSuchEntityException;

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
     * @var \Magento\Quote\Api\CartItemRepositoryInterface
     */
    protected $cartItemRepository;

    /**
     * QuoteItemExtractor constructor.
     * @param \Magento\Quote\Api\CartItemRepositoryInterface $cartItemRepository
     */
    public function __construct(\Magento\Quote\Api\CartItemRepositoryInterface $cartItemRepository)
    {
        $this->cartItemRepository = $cartItemRepository;
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
            $cartItems = $this->cartItemRepository->getList($quoteId);
        } catch (NoSuchEntityException $e) {
            return null;
        }
        foreach ($cartItems as $item) {
            $this->items[$quoteId][$item->getItemId()] = $item;
        }
        return $this->items[$quoteId][$itemId] ?? null;
    }
}
