<?php
declare(strict_types=1);

namespace Punchout2Go\Punchout\Model\Transfer\QuoteDataHandlers;

use Punchout2Go\Punchout\Model\Transfer\QuoteDataHandlerInterface;
use Magento\Weee\Helper\Data as WeeHelper;

/**
 * Class FixedProductTax
 * @package Punchout2Go\Punchout\Model\Transfer\QuoteDataHandlers
 */
class FixedProductTax implements QuoteDataHandlerInterface
{
    /**
     * @var ModuleHelperInterface
     */
    private $helper;

    /**
     * @param ProductMetadataInterface $metadata
     */
    public function __construct(WeeHelper $helper)
    {
        $this->helper = $helper;
    }

    /**
     * @param \Magento\Quote\Api\Data\CartInterface $cart
     * @return array
     */
    public function handle(\Magento\Quote\Api\Data\CartInterface $cart): array
    {
        return ['fixed_product_tax' => $this->helper->getTotalAmounts($cart->getItems(), $cart->getStore())];
    }
}
