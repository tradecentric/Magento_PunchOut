<?php
declare(strict_types=1);

namespace Punchout2Go\Punchout\Model\Transfer;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Quote\Api\Data\CartItemInterface;
use Punchout2Go\Punchout\Api\Data\ItemTransferDtoInterface;
use Punchout2Go\Punchout\Api\TransferCartItemDataInterface;
use Punchout2Go\Punchout\Model\Transfer\QuoteDataHandlers\Items\ItemOptionsResolver;
use Punchout2Go\Punchout\Helper\Transfer;

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
     * @var Transfer
     */
    protected $helper;

    /**
     * QuoteItemOptionsTransferData constructor.
     * @param ItemDataResolver $itemDataResolver
     * @param itemDataResolver $optionDataResolver
     * @param ProductRepositoryInterface $productRepository
     * @param ItemOptionsResolver $itemOptionsResolver
     */
    public function __construct(
        ItemDataPool $itemDataResolver,
        ItemDataPool $optionDataResolver,
        ProductRepositoryInterface $productRepository,
        ItemOptionsResolver $itemOptionsResolver,
        Transfer $helper
    ) {
        $this->itemDataResolver = $itemDataResolver;
        $this->optionDataResolver = $optionDataResolver;
        $this->productRepository = $productRepository;
        $this->itemOptionsResolver = $itemOptionsResolver;
        $this->helper = $helper;
    }

    /**
     * @param ItemTransferDtoInterface $dto
     * @return bool
     */
    public function supports(ItemTransferDtoInterface $dto): bool
    {
        return $this->helper->isSplitOptionSkus($dto->getStoreId()) && count($this->itemOptionsResolver->resolve($dto->getItem()));
    }

    /**
     * @param ItemTransferDtoInterface $dto
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getData(ItemTransferDtoInterface $dto): array
    {
        $result = [];
        $parentItem = [];
        $data = [$dto->getItem(), $this->getProduct($dto)];
        foreach ($data as $object) {
            $resolver = $this->itemDataResolver->get($object);
            $parentItem = array_merge($parentItem, $resolver->handle($object, $dto->getStoreId()));
        }
        $result[] = $parentItem;
        foreach ($this->itemOptionsResolver->resolve($dto->getItem()) as $option) {
            $child = [];
            foreach (array_merge($data, [$option]) as $object) {
                $resolver = $this->optionDataResolver->get($object);
                $child = array_merge($child, $resolver->handle($object, $dto->getStoreId()));
            }
            $result[] = $child;
        }
        return $result;
    }

    /**
     * @param ItemTransferDtoInterface $dto
     * @return \Magento\Catalog\Api\Data\ProductInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function getProduct(ItemTransferDtoInterface $dto)
    {
        return $this->productRepository->getById($dto->getItem()->getProductId(), false, $dto->getStoreId());
    }
}
