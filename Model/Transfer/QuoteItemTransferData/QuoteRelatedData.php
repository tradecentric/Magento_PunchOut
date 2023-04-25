<?php
declare(strict_types=1);

namespace Punchout2Go\Punchout\Model\Transfer\QuoteItemTransferData;

use Magento\Catalog\Model\ProductRepository;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Weee\Helper\Data as WeeHelper;
use Punchout2Go\PurchaseOrder\Helper\Data;

/**
 * Class QuoteRelatedData
 * @package Punchout2Go\Punchout\Model\Transfer\QuoteItemTransferData
 */
class QuoteRelatedData implements QuoteItemRelatedDataHandlerInterface
{
    /**
     * @var WeeHelper
     */
    protected $weeHelper;

    /**
     * @var ProductRepository
     */
    protected $productRepository;

    /**
     * @var array
     */
    protected $mapping = [];

    /**
     * QuoteRelatedData constructor.
     * @param array $mapping
     */
    public function __construct(
        WeeHelper $weeHelper,
        ProductRepository $repository,
        array $mapping = []
    )
    {
        $this->mapping = $mapping;
        $this->weeHelper = $weeHelper;
        $this->productRepository = $repository;
    }

    /**
     * map from item
     *
     * @param CartItemInterface $cartItem
     * @param $storeId
     * @return array
     */
    public function handle(CartItemInterface $cartItem, $storeId): array
    {
        $result = [];
        foreach ($this->mapping as $fieldCode => $field) {
            if (is_object($field) && ($field instanceof QuoteItemRelatedDataHandlerInterface)) {
                $result = array_merge($result, $field->handle($cartItem, $storeId));
                continue;
            }
            $result[$fieldCode] = $cartItem->getData($field);
        }

        $weeTax = $cartItem->getWeeeTaxApplied();
        if ($weeTax) {
            $weeTax = json_decode($weeTax, true);
            foreach ($weeTax as $tax) {
                $result[Data::prepareTaxTotalName($tax['title'])] = $tax['row_amount'];
            }
        }

        /** @var Magento\Catalog\Model\Product $product */
        $product = $this->productRepository->getById($cartItem->getProduct()->getId());
        foreach ($this->weeHelper->getProductWeeeAttributes($cartItem->getProduct()) as $item) {
            $result[$item->getCode()] = $product->getData($item->getCode());
        }

        return $result;
    }
}
