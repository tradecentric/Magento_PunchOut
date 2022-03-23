<?php
declare(strict_types=1);

namespace Punchout2Go\Punchout\Helper;

use Magento\Catalog\Helper\Product\Configuration\ConfigurationInterface;
use Magento\Catalog\Model\Product\Configuration\Item\ItemInterface;
use Magento\Catalog\Helper\Product\Configuration as MagentoConfiguration;
use Magento\Framework\Exception\LocalizedException;

/**
 * @package Punchout2Go\Punchout\Helper\Product
 */
class ProductConfiguration implements ConfigurationInterface
{
    /**
     * @var MagentoConfiguration
     */
    protected $configuration;

    /**
     * @param MagentoConfiguration $configuration
     */
    public function __construct(MagentoConfiguration $configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * @param ItemInterface $item
     * @return array
     */
    public function getOptions(ItemInterface $item): array
    {
        $result = [];
        $product = $item->getProduct();
        $attributes = array_map(function (array $attribute) {
            $attribute['options'] = array_column($attribute['options'], 'value');
            return $attribute;
        }, $product->getTypeInstance()->getConfigurableAttributesAsArray($product));
        foreach ($this->configuration->getOptions($item) as $option) {
            $option['attribute_code'] = $this->getAttributeCodeForOption($attributes, $option['option_value']);
            $result[] = $option;
        }
        return $result;
    }

    /**
     * @param array $attributes
     * @param $optionId
     * @return string
     */
    protected function getAttributeCodeForOption(array $attributes, $optionId): string
    {
        foreach ($attributes as $attribute) {
            if (in_array($optionId, $attribute['options'])) {
                return $attribute['attribute_code'];
            }
        }
        throw new LocalizedException(__('Attribute for option is not found'));
    }
}
