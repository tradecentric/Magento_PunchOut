<?php
declare(strict_types=1);

namespace Punchout2Go\Punchout\Model\PunchoutSessionCollector\QuoteHandler;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CartRepositoryInterface;
use Punchout2Go\Punchout\Api\LoggerInterface;

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
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * QuoteItemExtractor constructor.
     * @param CartRepositoryInterface $cartRepository
     * @param LoggerInterface $logger
     */
    public function __construct(CartRepositoryInterface $cartRepository, LoggerInterface $logger)
    {
        $this->cartRepository = $cartRepository;
        $this->logger = $logger;
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
        $this->logger->log(sprintf('QuoteItemExtractor: looking up quote_id=%s item_id=%s', $quoteId, $itemId));
        try {
            $quote = $this->cartRepository->get((int) $quoteId);
        } catch (NoSuchEntityException $e) {
            $this->logger->log(sprintf('QuoteItemExtractor: quote %s not found (NoSuchEntityException: %s)', $quoteId, $e->getMessage()));
            return null;
        }
        $allItems = (array) $quote->getItems();
        $this->logger->log(sprintf(
            'QuoteItemExtractor: quote %s loaded (is_active=%s, customer_id=%s, items_count=%d, item_ids=[%s])',
            $quoteId,
            var_export($quote->getIsActive(), true),
            (string) $quote->getCustomerId(),
            count($allItems),
            implode(',', array_map(static fn($i) => (string) $i->getItemId(), $allItems))
        ));
        foreach ($allItems as $item) {
            $this->items[$quoteId][$item->getItemId()] = $item;
        }
        return $this->items[$quoteId][$itemId] ?? null;
    }
}
