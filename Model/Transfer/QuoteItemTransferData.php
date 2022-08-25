<?php
declare(strict_types=1);

namespace Punchout2Go\Punchout\Model\Transfer;

use Magento\Quote\Api\Data\CartItemInterface;
use Punchout2Go\Punchout\Api\Data\ItemTransferDtoInterface;
use Punchout2Go\Punchout\Api\TransferCartItemDataInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;

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
        ProductRepositoryInterface $productRepository,
        ItemDataPool $dataResolver
    ) {
        $this->dataResolver = $dataResolver;
        $this->productRepository = $productRepository;
    }

    /**
     * @param ItemTransferDtoInterface $dto
     * @return bool
     */
    public function supports(ItemTransferDtoInterface $dto): bool
    {
        return true;
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
        $data = [$dto->getItem(), $this->getProduct($dto)];
        foreach ($data as $object) {
            $resolver = $this->dataResolver->get($object);
            $result = array_merge($result, $resolver->handle($object, $dto->getStoreId()));
        }
        return [$result];
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
