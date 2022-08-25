<?php
declare(strict_types=1);

namespace Punchout2Go\Punchout\Model\Transfer\QuoteItemTransferData;

use Magento\Quote\Api\Data\CartItemInterface;

/**
 * Class QuoteRelatedData
 * @package Punchout2Go\Punchout\Model\Transfer\QuoteItemTransferData
 */
class QuoteRelatedData implements QuoteItemRelatedDataHandlerInterface
{
    /**
     * @var array
     */
    protected $mapping = [];

    /**
     * QuoteRelatedData constructor.
     * @param array $mapping
     */
    public function __construct(array $mapping = [])
    {
        $this->mapping = $mapping;
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
        return $result;
    }
}
