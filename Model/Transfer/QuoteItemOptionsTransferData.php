<?php
declare(strict_types=1);

namespace Punchout2Go\Punchout\Model\Transfer;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Quote\Api\Data\CartItemInterface;
use Punchout2Go\Punchout\Api\TransferCartItemDataInterface;
use Punchout2Go\Punchout\Model\Transfer\QuoteDataHandlers\Items\ItemOptionsResolver;

/**
 * Class QuoteItemTransferData
 * @package Punchout2Go\Punchout\Model\Transfer
 */
class QuoteItemOptionsTransferData implements TransferCartItemDataInterface
{
    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var ItemDataResolver
     */
    protected $itemDataResolver;

    /**
     * @var itemDataResolver
     */
    protected $optionDataResolver;

    /**
     * @var ItemOptionsResolver
     */
    protected $itemOptionsResolver;

    /**
     * QuoteItemOptionsTransferData constructor.
     * @param ItemDataResolver $itemDataResolver
     * @param itemDataResolver $optionDataResolver
     * @param ProductRepositoryInterface $productRepository
     * @param ItemOptionsResolver $itemOptionsResolver
     */
    public function __construct(
        ItemDataResolver $itemDataResolver,
        itemDataResolver $optionDataResolver,
        ProductRepositoryInterface $productRepository,
        ItemOptionsResolver $itemOptionsResolver
    ) {
        $this->itemDataResolver = $itemDataResolver;
        $this->optionDataResolver = $optionDataResolver;
        $this->productRepository = $productRepository;
        $this->itemOptionsResolver = $itemOptionsResolver;
    }

    /**
     * @param CartItemInterface $cartItem
     * @param null $storeId
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getData(CartItemInterface $cartItem, $storeId = null): array
    {
        $result = [];
        $parentItem = [];
        $data = [$cartItem, $this->getProduct($cartItem)];
        foreach ($data as $object) {
            $resolver = $this->itemDataResolver->resolve($object);
            $parentItem = array_merge($parentItem, $resolver->handle($object, $storeId));
        }
        $result[] = $parentItem;
        foreach ($this->itemOptionsResolver->resolve($cartItem) as $option) {
            $child = [];
            foreach (array_merge($data, [$option]) as $object) {
                $resolver = $this->optionDataResolver->resolve($object);
                $child = array_merge($child, $resolver->handle($object, $storeId));
            }
            $result[] = $child;
        }
        return $result;
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
