<?php
declare(strict_types=1);

namespace Punchout2Go\Punchout\Model\Transfer\QuoteItemTransferData\ProductProvider;

use Magento\Catalog\Model\Product;
use Magento\Quote\Model\Quote\Item;
use Punchout2Go\Punchout\Api\ProductProviderInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;

class DefaultProvider implements ProductProviderInterface
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
     * @param Item $item
     * @return Product
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getProductFromQuoteItem(Item $item): Product
    {
        return $this->productRepository->getById($item->getProductId(), false, $item->getStoreId());
    }
}