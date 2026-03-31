<?php
declare(strict_types=1);

namespace Punchout2Go\Punchout\Model\PunchoutSessionCollector;

use Magento\Framework\Exception\LocalizedException as SessionException;
use Magento\Framework\Exception\NoSuchEntityException;
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
        $object->getQuote()->setCustomer($object->getCustomer());
        $checkoutData = $this->dataExtractor->extract($object->getSession()->getParams());
        foreach ($checkoutData['items'] as $item) {
            $this->logger->log(sprintf('Add item sku %s : quote_id\item_id %s', $item['sku'], $item['line_id']));
            $quoteItem = $this->getQuoteItem($item);
            if (!$quoteItem) {
                $this->logger->log('No quote item found sku ' . $item['sku']);
                continue;
            }
            $infoBuyRequest = $this->getInfoByRequest($quoteItem, $item);
            $product = $this->getProduct($infoBuyRequest['product'], $item['sku']);
            if (!$product) {
                $this->logger->log('No product found');
                continue;
            }
            $object->getQuote()->addProduct($product, $infoBuyRequest);
        }
        $this->logger->log('Quote Setup Complete');
    }

    /**
     * @param array $item
     * @return null
     */
    protected function getQuoteItem(array $item)
    {
        if (!$item['line_id']) {
            return null;
        }
        list($quoteId, $itemId) = $this->helper->getQuoteItemIdInfo($item['line_id']);
        return $this->quoteItemExtractor->getQuoteItem($quoteId, $itemId);
    }

    /**
     * @param $productId
     * @param $sku
     * @return \Magento\Catalog\Api\Data\ProductInterface|null
     */
    protected function getProduct($productId, $sku)
    {
        try {
            $product = null;
            if ((int) $productId) {
                $product = $this->productRepository->getById((int) $productId);
            } elseif ($sku) {
                $product = $this->productRepository->getById((string) $sku);
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
}
