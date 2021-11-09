<?php
declare(strict_types=1);

namespace Punchout2Go\Punchout\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;

/**
 * Class Transfer
 * @package Punchout2Go\Punchout\Helper
 */
class Transfer extends \Magento\Framework\App\Helper\AbstractHelper
{
    const XML_PATH_BUTTON_LABEL = 'punchout2go_punchout/display/transfer_button_label';
    const XML_PATH_BUTTON_HELP = 'punchout2go_punchout/display/transfer_button_help';
    const XML_PATH_BUTTON_CSS = 'punchout2go_punchout/display/transfer_button_css_class';
    const XML_PATH_API_KEY = 'punchout2go_punchout/system/api_key';
    const XML_PATH_INCLUDE_SHIPPING = 'punchout2go_punchout/order/include_shipping';
    const XML_PATH_INCLUDE_TAX = 'punchout2go_punchout/order/include_tax';
    const XML_PATH_INCLUDE_DISCOUNT = 'punchout2go_punchout/order/include_discount';
    const XML_PATH_SPLIT_SKUS = 'punchout2go_punchout/order/separate_customized_skus';
    const XML_PATH_PRODUCT_CLASSIFICATION_FIELD = 'punchout2go_punchout/order/classification_field';
    const XML_PATH_PRODUCT_CLASSIFICATION = 'punchout2go_punchout/defaults/classification';
    const XML_PATH_UOM_FIELD = 'punchout2go_punchout/order/uom_field';
    const XML_PATH_UOM_DEFAULT = 'punchout2go_punchout/defaults/uom';
    const XML_PATH_ITEM_MAP = 'punchout2go_punchout/order/data_item_return_map';
    const XML_PATH_CART_MAP = 'punchout2go_punchout/order/data_cart_return_map';
    const XML_PATH_DISALLOW_EDIT = 'punchout2go_punchout/order/disallow_edit_cart';
    const XML_PATH_DEFAULT_LANGUAGE = 'punchout2go_punchout/order/default_language';

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    protected $jsonSerializer;

    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    protected $metadata;

    /**
     * Transfer constructor.
     * @param \Magento\Framework\Serialize\Serializer\Json $jsonSerializer
     * @param \Magento\Framework\App\ProductMetadataInterface $metadata
     * @param Context $context
     */
    public function __construct(
        \Magento\Framework\Serialize\Serializer\Json $jsonSerializer,
        \Magento\Framework\App\ProductMetadataInterface $metadata,
        Context $context
    ) {
        $this->jsonSerializer = $jsonSerializer;
        $this->metadata = $metadata;
        parent::__construct($context);
    }

    /**
     * @param null $store
     * @return mixed
     */
    public function getButtonLabel($store = null)
    {
        return $this->scopeConfig->getValue(
            static::XML_PATH_BUTTON_LABEL,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @param null $store
     * @return mixed
     */
    public function getButtonHelp($store = null)
    {
        return $this->scopeConfig->getValue(
            static::XML_PATH_BUTTON_HELP,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @param null $store
     * @return mixed
     */
    public function getButtonCss($store = null)
    {
        return $this->scopeConfig->getValue(
            static::XML_PATH_BUTTON_CSS,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @param null $store
     * @return mixed
     */
    public function getApiKey($store = null)
    {
        return $this->scopeConfig->getValue(
            static::XML_PATH_API_KEY,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @param null $store
     * @return mixed
     */
    public function getProductClassificationField($store = null)
    {
        return $this->scopeConfig->getValue(
            static::XML_PATH_PRODUCT_CLASSIFICATION_FIELD,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @param null $store
     * @return mixed
     */
    public function getProductDefaultClassification($store = null)
    {
        return $this->scopeConfig->getValue(
            static::XML_PATH_PRODUCT_CLASSIFICATION,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @param null $store
     * @return mixed
     */
    public function getUomField($store = null)
    {
        return $this->scopeConfig->getValue(
            static::XML_PATH_UOM_FIELD,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @param null $store
     * @return mixed
     */
    public function getUomDefault($store = null)
    {
        return $this->scopeConfig->getValue(
            static::XML_PATH_UOM_DEFAULT,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @param null $store
     * @return bool
     */
    public function isIncludeShipping($store = null)
    {
        return $this->scopeConfig->isSetFlag(
            static::XML_PATH_INCLUDE_SHIPPING,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @param null $store
     * @return bool
     */
    public function isIncludeTax($store = null)
    {
        return $this->scopeConfig->isSetFlag(
            static::XML_PATH_INCLUDE_TAX,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @param null $store
     * @return bool
     */
    public function isIncludeDiscount($store = null)
    {
        return $this->scopeConfig->isSetFlag(
            static::XML_PATH_INCLUDE_DISCOUNT,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @param null $store
     * @return bool
     */
    public function isSplitOptionSkus($store = null)
    {
        return $this->scopeConfig->isSetFlag(
            static::XML_PATH_SPLIT_SKUS,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @param int $quoteId
     * @param int $quoteItemId
     * @return string
     */
    public function getLineId(int $quoteId, int $quoteItemId)
    {
        return implode("/", [$quoteId, $quoteItemId]);
    }

    /**
     * @param null $store
     * @return array
     */
    public function getCartItemMap($store = null)
    {
        $json = $this->scopeConfig->getValue(
            static::XML_PATH_ITEM_MAP,
            ScopeInterface::SCOPE_STORE,
            $store
        );
        return (array) $this->jsonSerializer->unserialize($json);
    }

    /**
     * @param null $store
     * @return array
     */
    public function getCartMap($store = null)
    {
        $json = $this->scopeConfig->getValue(
            static::XML_PATH_CART_MAP,
            ScopeInterface::SCOPE_STORE,
            $store
        );
        return (array) $this->jsonSerializer->unserialize($json);
    }

    /**
     * @param null $store
     * @return mixed
     */
    public function isDisallowEditCart($store = null)
    {
        return $this->scopeConfig->getValue(
            static::XML_PATH_DISALLOW_EDIT,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @param null $store
     * @return mixed
     */
    public function getDefaultLanguage($store = null)
    {
        return $this->scopeConfig->getValue(
            static::XML_PATH_DEFAULT_LANGUAGE,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @return string
     */
    public function getMagentoVersion(): string
    {
        return $this->metadata->getVersion();
    }
}
