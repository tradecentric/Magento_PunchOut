<?php
declare(strict_types=1);

namespace Punchout2Go\Punchout\Model\Transfer\QuoteItemTransferData\Fields;

use Punchout2Go\Punchout\Model\Transfer\QuoteDataHandlers\Items\OptionsContainerInterface;
use  Punchout2Go\Punchout\Model\Transfer\QuoteItemTransferData\OptionRelatedDataInterface;

/**
 * Class ProductOptionFields
 * @package Punchout2Go\Punchout\Model\Transfer\QuoteItemTransferData
 */
class ProductOptionFields implements OptionRelatedDataInterface
{
    /**
     * @param OptionsContainerInterface $container
     * @param null $storeId
     * @return array
     */
    public function handle(OptionsContainerInterface $container, $storeId = null): array
    {
        $optionValue  = null;
        $productOption = $container->getProductOption();
        $cartOption = $container->getItemOption();
        if ($productOption->getValues()) {
            $optionValue = $productOption->getValueById($cartOption->getValue());
        }
        $price = $optionValue ? $optionValue->getPrice() : $productOption->getPrice();
        $title = $optionValue ? $optionValue->getTitle() : $productOption->getTitle();
        $value = $optionValue ? $optionValue->getValue() : $cartOption->getValue();
        return [
            'supplierid' => $optionValue ? $optionValue->getSku() : $productOption->getSku(),
            'description' => $value ? $title . " : " . $value : $title,
            'option_title' => $productOption->getTitle(),
            'unitprice' => $price <= 0 ? 0 : $price,
            'title' => $title,
            'value' => $value,
        ];
    }
}
