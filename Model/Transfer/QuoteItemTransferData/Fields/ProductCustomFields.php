<?php
declare(strict_types=1);

namespace Punchout2Go\Punchout\Model\Transfer\QuoteItemTransferData\Fields;

use Magento\Catalog\Api\Data\ProductInterface;
use Punchout2Go\Punchout\Model\Transfer\QuoteItemTransferData\ProductRelatedDataHandlerInterface;

/**
 * Class CustomFields
 * @package Punchout2Go\Punchout\Model\Transfer\QuoteItemTransferData\Fields
 */
class ProductCustomFields implements ProductRelatedDataHandlerInterface
{
    /**
     * @var \Punchout2Go\Punchout\Helper\Transfer
     */
    protected $helper;

    /**
     * @var \Punchout2Go\Punchout\Helper\Data
     */
    protected $defaultHelper;

    /**
     * @var \Punchout2Go\Punchout\Model\Transfer\CustomFields\ProductPartResolver
     */
    protected $partFactory;

    /**
     * ProductCustomFields constructor.
     * @param \Punchout2Go\Punchout\Helper\Transfer $helper
     * @param \Punchout2Go\Punchout\Helper\Data $data
     * @param \Punchout2Go\Punchout\Model\Transfer\CustomFields\ProductPartResolver $partFactory
     */
    public function __construct(
        \Punchout2Go\Punchout\Helper\Transfer $helper,
        \Punchout2Go\Punchout\Helper\Data $data,
        \Punchout2Go\Punchout\Model\Transfer\CustomFields\ProductPartResolver $partFactory
    ) {
        $this->helper = $helper;
        $this->defaultHelper = $data;
        $this->partFactory = $partFactory;
    }

    /**
     * @param ProductInterface $product
     * @param null $storeId
     * @return mixed[]
     */
    public function handle(ProductInterface $product, $storeId = null): array
    {
        $result = [];
        $fields = $this->helper->getCartItemMap();
		
var_dump('ProductCustomFields - ' . $fields);
exit(0);
	
        if (!$fields) {
            return $result;
        }
        foreach ($fields as $field) {
            list($source, $destination) = $this->defaultHelper->prepareSource($field);
            if (strlen($source) && strlen($destination) && ($val = $this->getMapSourceValue($source, $product))) {
                $result[$destination] = $val;
            }
        }
        return $result;
    }

    /**
     * @param string $path
     * @param ProductInterface $product
     * @return string
     */
    protected function getMapSourceValue(string $path, ProductInterface $product)
    {
        $s = [];
        if (!preg_match('/^([^:]+):([^:]+)$/', $path, $s)) {
            return '';
        }
        $part = $s[1];
        $path = $s[2];
        $handler = $this->partFactory->resolve($part);
        if (!$handler) {
            return '';
        }
        return $handler->handle($product, $path);
    }
}
