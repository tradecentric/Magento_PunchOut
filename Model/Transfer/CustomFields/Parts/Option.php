<?php
declare(strict_types=1);

namespace Punchout2Go\Punchout\Model\Transfer\CustomFields\Parts;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Quote\Api\Data\CartItemInterface;
use Punchout2Go\Punchout\Model\Transfer\CustomFields\CartItemPartInterface;

/**
 * Class PartFactory
 * @package Punchout2Go\Punchout\Model\Transfer\CustomFields
 */
class Option  implements CartItemPartInterface
{
    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    protected $jsonSerializer;

    /**
     * Option constructor.
     * @param \Magento\Framework\Serialize\Serializer\Json $jsonSerializer
     */
    public function __construct(\Magento\Framework\Serialize\Serializer\Json $jsonSerializer)
    {
        $this->jsonSerializer = $jsonSerializer;
    }

    /**
     * @param CartItemInterface $model
     * @param string $path
     * @return string
     */
    public function handle(CartItemInterface $model, string $path): string
    {
        //there is no way to get at the options we're after other than getting all of the options as an array.
        $options = $model->getProduct()->getTypeInstance(true)->getOrderOptions($model->getProduct());
        //use $path passed in as a key (something like "simple_sku" or "info_buyRequest") into the options array
        // and return that value serialized if it exists
        $option  = array_key_exists($path, $options) && isset($options[$path]) ? $options[$path] : null;
        if ($option) {
            return $this->jsonSerializer->serialize($option);
        }
        return '';
    }
}
