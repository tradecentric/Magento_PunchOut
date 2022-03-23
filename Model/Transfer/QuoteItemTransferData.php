<?php
declare(strict_types=1);

namespace Punchout2Go\Punchout\Model\Transfer;

use Magento\Quote\Api\Data\CartItemInterface;
use Punchout2Go\Punchout\Api\TransferCartItemDataInterface;
use Punchout2Go\Punchout\Model\Transfer\QuoteItemTransferData\ProductRelatedDataHandlerInterface;
use Punchout2Go\Punchout\Model\Transfer\QuoteItemTransferData\QuoteItemRelatedDataHandlerInterface;

/**
 * Class QuoteItemTransferData
 * @package Punchout2Go\Punchout\Model\Transfer
 */
class QuoteItemTransferData implements TransferCartItemDataInterface
{
    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var ItemDataResolver
     */
    protected $dataResolver;

    /**
     * QuoteItemTransferData constructor.
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param ItemDataResolver $dataResolver
     */
    public function __construct(
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        ItemDataResolver $dataResolver
    ) {
        $this->dataResolver = $dataResolver;
        $this->productRepository = $productRepository;
    }

    /**
     * @return array
     */
    public function getData(CartItemInterface $cartItem, $storeId = null): array
    {
        $result = [];
        $data = [$cartItem, $this->getProduct($cartItem)];
        foreach ($data as $object) {
            $resolver = $this->dataResolver->resolve($object);
            $result = array_merge($result, $resolver->handle($object, $storeId));
        }
        return [$result];
    }

    /**
     * @param CartItemInterface $cartItem
     * @param null $storeId
     * @return \Magento\Catalog\Api\Data\ProductInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function getProduct(CartItemInterface $cartItem, $storeId = null)
    {
        return $this->productRepository->getById($cartItem->getProductId(), false, $storeId);
    }
}
