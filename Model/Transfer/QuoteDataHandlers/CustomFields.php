<?php
declare(strict_types=1);

namespace Punchout2Go\Punchout\Model\Transfer\QuoteDataHandlers;

use Magento\Quote\Api\Data\CartInterface;
use Punchout2Go\Punchout\Model\Transfer\QuoteDataHandlerInterface;

/**
 * Class Discount
 * @package Punchout2Go\Punchout\Model\Transfer\QuoteDataHandlers
 */
class CustomFields implements QuoteDataHandlerInterface
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
     * CustomFields constructor.
     * @param \Punchout2Go\Punchout\Helper\Transfer $transferHelper
     * @param \Punchout2Go\Punchout\Helper\Data $data
     */
    public function __construct(
        \Punchout2Go\Punchout\Helper\Transfer $transferHelper,
        \Punchout2Go\Punchout\Helper\Data $data
    ) {
        $this->helper = $transferHelper;
        $this->defaultHelper = $data;
    }

    /**
     * @param \Magento\Quote\Api\Data\CartInterface $cart
     * @return array
     */
    public function handle(CartInterface $cart): array
    {
        $result = [];
        $cartMap = $this->helper->getCartMap();
        foreach ($cartMap as $field) {
            list($source, $destination) = $this->defaultHelper->prepareSource($field);
            if (strlen($source) && strlen($destination)) {
                $result[] = ['field' => $destination, 'value' => $this->getCustomCartSourceValue($cart, $source)];
            }
        }
        return ['custom_fields' => $result];
    }

    /**
     * @param CartInterface $cart
     * @param $path
     * @return mixed|string|null
     */
    protected function getCustomCartSourceValue(CartInterface $cart, $path)
    {
        if (preg_match('/^([^:]+):([^:]+)$/', $path, $s)) {
            $part = $s[1];
            $path = $s[2];
            if ($part == 'literal') {
                return $path;
            }
        }
        return $cart->getData($path);
    }
}