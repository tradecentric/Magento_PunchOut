<?php
declare(strict_types=1);

namespace Punchout2Go\Punchout\Model\Transfer\QuoteItemTransferData\ProductProvider;

use Magento\Catalog\Model\Product;
use Magento\Quote\Model\Quote\Item;
use Punchout2Go\Punchout\Api\ProductProviderInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;

class Configurable implements ProductProviderInterface
{
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(ProductRepositoryInterface $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    /**
     * Get simple item from configurable
     * 
     * @param Item $item
     * 
     * @return Product
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getProductFromQuoteItem(Item $item): Product
    {
        $productId = $item->getProductId();
        $simpleOption = $item->getOptionByCode('simple_product');
        if ($simpleOption) {
            $productId = $simpleOption->getProductId();
        }
        return $this->productRepository->getById($productId, false, $item->getStoreId());
    }
}
