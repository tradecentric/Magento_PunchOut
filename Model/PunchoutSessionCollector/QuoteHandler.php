<?php
declare(strict_types=1);

namespace Punchout2Go\Punchout\Model\PunchoutSessionCollector;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Exception\LocalizedException as SessionException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Api\Data\CartItemInterface;
use Punchout2Go\Punchout\Api\EntityHandlerInterface;
use Punchout2Go\Punchout\Api\SessionContainerInterface;
use Punchout2Go\Punchout\Model\PunchoutSessionCollector\QuoteHandler\QuoteItemExtractor;

/**
 * Class CustomerHandler
 * @package Punchout2Go\Punchout\Model
 */
class QuoteHandler implements EntityHandlerInterface
{
    /**
     * @var \Punchout2Go\Punchout\Model\DataExtractorInterface
     */
    protected $dataExtractor;

    /**
     * @var \Punchout2Go\Punchout\Helper\Data
     */
    protected $helper;

    /**
     * @var QuoteHandler\QuoteItemExtractor
     */
    protected $quoteItemExtractor;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \Punchout2Go\Punchout\Api\LoggerInterface
     */
    protected $logger;

    /**
     * QuoteHandler constructor.
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Punchout2Go\Punchout\Model\DataExtractorInterface $dataExtractor
     * @param \Punchout2Go\Punchout\Model\QuoteHandler\QuoteItemExtractor $quoteItemExtractor
     * @param \Punchout2Go\Punchout\Helper\Data $helper
     * @param \Punchout2Go\Punchout\Api\LoggerInterface $logger
     * @param \Magento\Checkout\Model\Session $checkoutSession
     */
    public function __construct(
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Punchout2Go\Punchout\Model\DataExtractorInterface $dataExtractor,
        QuoteItemExtractor $quoteItemExtractor,
        \Punchout2Go\Punchout\Helper\Data $helper,
        \Punchout2Go\Punchout\Api\LoggerInterface $logger
    ) {
        $this->dataExtractor = $dataExtractor;
        $this->productRepository = $productRepository;
        $this->quoteItemExtractor = $quoteItemExtractor;
        $this->helper = $helper;
        $this->logger = $logger;
    }

    /**
     * @param SessionContainerInterface $object
     * @throws NoSuchEntityException
     * @throws SessionException
     */
    public function handle(SessionContainerInterface $object)
    {
        $this->logger->log('Quote Setup Begin');

        $quote = $object->getQuote();
        $quote->setCustomer($object->getCustomer());

        // Index current cart items twice:
        //  - by item_id for stable supplierauxid matching (post-stable-IDs steady state)
        //  - by SKU as a list per SKU. to avoid the last-indexed item
        //    silently win the fallback and have the wrong line updated or removed
        $byItemId = [];
        $bySku    = [];
        foreach ($quote->getAllVisibleItems() as $existing) {
            $byItemId[(int) $existing->getItemId()] = $existing;
            $bySku[(string) $existing->getSku()][]  = $existing;
        }

        $checkoutData = $this->dataExtractor->extract($object->getSession()->getParams());
        $kept = [];

        foreach ($checkoutData['items'] as $item) {
            $matched = $this->findMatch($item, $quote, $byItemId, $bySku, $kept);

            if ($matched) {
                // Existing item — update qty in place. quote_item_id (and therefore
                // supplierauxid) is preserved across the round-trip.
                $matched->setQty((float) $item['qty']);
                $kept[(int) $matched->getItemId()] = true;
                $this->logger->log(sprintf(
                    'Reconciled item (sku=%s, item_id=%d, qty=%s)',
                    $item['sku'],
                    $matched->getItemId(),
                    $item['qty']
                ));
                continue;
            }

            $this->logger->log(sprintf(
                'New item from procurement (sku=%s, line_id=%s)',
                $item['sku'],
                $item['line_id']
            ));

            $product = $this->getProduct(null, $item['sku']);
            if (!$product) {
                $this->logger->log('No product found for sku ' . $item['sku']);
                continue;
            }
            $result = $quote->addProduct($product, (float) $item['qty']);
            if (is_string($result)) {
                $this->logger->log(sprintf(
                    'Failed to add product to quote (sku=%s, line_id=%s): %s',
                    $item['sku'],
                    $item['line_id'],
                    $result
                ));
            }
        }

        foreach ($byItemId as $itemId => $existing) {
            if (!isset($kept[$itemId])) {
                $quote->removeItem($itemId);
                $this->logger->log(sprintf(
                    'Removed item not present in inbound payload (sku=%s, item_id=%d)',
                    $existing->getSku(),
                    $itemId
                ));
            }
        }

        $this->logger->log('Quote Setup Complete');
    }

    /**
     * @param $productId
     * @param $sku
     * @return ProductInterface|null
     */
    protected function getProduct($productId, $sku): ?ProductInterface
    {
        try {
            $product = null;
            if ((int) $productId) {
                $product = $this->productRepository->getById((int) $productId);
            } elseif ($sku) {
                $product = $this->productRepository->get((string) $sku);
            }
        } catch (NoSuchEntityException $e) {
        }
        return $product;
    }

    /**
     * @param \Magento\Quote\Api\Data\CartItemInterface $quoteItem
     * @param array $item
     * @return \Magento\Framework\DataObject|mixed
     */
    protected function getInfoByRequest(\Magento\Quote\Api\Data\CartItemInterface $quoteItem, array $item)
    {
        $infoBuyRequest = $quoteItem->getBuyRequest();
        if (!$infoBuyRequest) {
            return $item['qty'];
        }
        if (isset($infoBuyRequest['uenc'])) {
            unset($infoBuyRequest['uenc']);
        }
        if (!isset($infoBuyRequest['product'])) {
            $infoBuyRequest['product'] = $quoteItem->getProductId();
        }
        $infoBuyRequest['qty'] = $item['qty'];
        return $infoBuyRequest;
    }

    /**
     * Resolve which existing cart item, if any, an inbound payload row corresponds to.
     *
     * Primary match: inbound secondaryId is "{quote_id}/{item_id}". With stable IDs,
     * the quote_id portion equals the customer's current quote, and the item_id
     * portion identifies the row directly.
     *
     * Fallback match: by SKU, but only when exactly one existing visible item
     * carries that SKU. The inbound payload has no option data (super_attribute /
     * bundle_option / custom options), so we cannot disambiguate when multiple
     * visible items share a SKU. Returning null in the ambiguous case lets the
     * caller route the row through addProduct() and the cleanup pass drop any
     * un-kept duplicates — preferable to silently mutating the wrong line.
     *
     * @param array         $item
     * @param CartInterface $quote
     * @param array         $byItemId
     * @param array         $bySku
     * @param array         $kept
     * @return CartItemInterface|null
     */
    protected function findMatch(
        array $item,
        CartInterface $quote,
        array $byItemId,
        array $bySku,
        array $kept
    ): ?CartItemInterface {
        if (!empty($item['line_id'])) {
            [$inboundQuoteId, $inboundItemId] = $this->helper->getQuoteItemIdInfo($item['line_id']);
            $itemId = (int) $inboundItemId;
            if ($itemId
                && (int) $inboundQuoteId === (int) $quote->getId()
                && isset($byItemId[$itemId])
                && !isset($kept[$itemId])
            ) {
                return $byItemId[$itemId];
            }
        }

        $sku = (string) ($item['sku'] ?? '');
        if ($sku === '' || empty($bySku[$sku])) {
            return null;
        }

        $candidates = [];
        foreach ($bySku[$sku] as $existing) {
            if (!isset($kept[(int) $existing->getItemId()])) {
                $candidates[] = $existing;
            }
        }
        if (count($candidates) === 1) {
            return $candidates[0];
        }
        if (count($candidates) > 1) {
            $this->logger->log(sprintf(
                'SKU fallback declined: %d unmatched visible items share sku=%s; routing inbound row as new',
                count($candidates),
                $sku
            ));
        }
        return null;
    }
}
