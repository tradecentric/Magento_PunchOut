<?php
declare(strict_types=1);

namespace Punchout2Go\Punchout\Model\Transfer\QuoteItemTransferData;

use Magento\Catalog\Model\Product;
use Magento\Framework\ObjectManagerInterface;
use Magento\Quote\Model\Quote\Item;
use Punchout2Go\Punchout\Api\ProductProviderInterface;
use Punchout2Go\Punchout\Model\Transfer\QuoteItemTransferData\ProductProvider\DefaultProvider;

class ProductProvider implements ProductProviderInterface
{
    /**
     * @var ObjectManagerInterface 
     */
    private $objectManager;

    /**
     * @var array 
     */
    private $productByTypeProviders = [];

    /**
     * @param ObjectManagerInterface $objectManager
     * @param array $productByTypeProviders
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        array $productByTypeProviders = []
    ) {
        $this->objectManager = $objectManager;
        $this->productByTypeProviders = $productByTypeProviders;
    }

    /**
     * @param Item $item
     * @return Product
     */
    public function getProductFromQuoteItem(Item $item): Product
    {
        $class = DefaultProvider::class;
        if (isset($this->productByTypeProviders[$item->getProductType()])) {
            $class = $this->productByTypeProviders[$item->getProductType()];
        }
        $productProvider = $this->objectManager->create($class);
        return $productProvider->getProductFromQuoteItem($item);
    }
}