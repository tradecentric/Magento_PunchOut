<?php
declare(strict_types=1);

namespace Punchout2Go\Punchout\Model\Transfer\QuoteItemTransferData\Fields;

use Magento\Quote\Api\Data\CartItemInterface;
use Punchout2Go\Punchout\Model\Transfer\QuoteItemTransferData\QuoteItemRelatedDataHandlerInterface;
use Magento\Catalog\Helper\Product\Configuration as MagentoConfiguration;

class Options implements QuoteItemRelatedDataHandlerInterface
{
    /**
     * @var MagentoConfiguration
     */
    private $configuration;

    /**
     * @param MagentoConfiguration $configuration
     */
    public function __construct(MagentoConfiguration $configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * Get cart item options
     *
     * @param CartItemInterface $cartItem
     * @param $storeId
     *
     * @return mixed[]
     */
    public function handle(CartItemInterface $cartItem, $storeId): array
    {
        $options = $this->configuration->getOptions($cartItem);
        if (!$options) {
            return [];
        }
        $values = array_map(function(array $option) {
            return $option['value'] ?? '';
        }, $options);
        return ['options' => $values];
    }
}
