<?php
declare(strict_types=1);

namespace Punchout2Go\Punchout\Model\Transfer;

use Magento\Quote\Api\Data\CartItemInterface;
use Punchout2Go\Punchout\Api\Data\ItemTransferDtoInterface;
use Punchout2Go\Punchout\Api\ProductProviderInterface;
use Punchout2Go\Punchout\Api\TransferCartItemDataInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;

/**
 * Class QuoteItemTransferData
 * @package Punchout2Go\Punchout\Model\Transfer
 */
class QuoteItemTransferData implements TransferCartItemDataInterface
{
    /**
     * @var ProductProviderInterface
     */
    protected $productProvider;

    /**
     * @var ItemDataResolver
     */
    protected $dataResolver;

    /**
     * @param ProductProviderInterface $productProvider
     * @param ItemDataPool $dataResolver
     */
    public function __construct(
        ProductProviderInterface $productProvider,
        ItemDataPool $dataResolver
    ) {
        $this->dataResolver = $dataResolver;
        $this->productProvider = $productProvider;
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
     * @return mixed[]
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getData(ItemTransferDtoInterface $dto): array
    {
        $result = [];
        $data = [$dto->getItem(), $this->productProvider->getProductFromQuoteItem($dto->getItem())];
        foreach ($data as $object) {
            $resolver = $this->dataResolver->get($object);
            $result = array_merge($result, $resolver->handle($object, $dto->getStoreId()));
        }
        return [$result];
    }
}
