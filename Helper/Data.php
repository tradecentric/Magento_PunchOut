<?php
declare(strict_types=1);

namespace Punchout2Go\Punchout\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Store\Model\ScopeInterface;

/**
 * Class Data
 * @package Punchout2Go\Punchout\Helper
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const XML_PATH_IS_LOG = 'punchout2go_punchout/system/logging';
    const XML_PATH_IS_VALIDATE_SESSION = 'punchout2go_punchout/security/punchout_validate_session';
    const XML_PATH_VALIDATE_SESSION_URL = 'punchout2go_punchout/security/punchout_validate_session_url';
    const XML_PATH_IS_SESSION_ENCRYPTED = 'punchout2go_punchout/security/punchout_encrypt';
    const XML_PATH_SESSION_ENCRYPTION_KEY = 'punchout2go_punchout/security/punchout_encryption_key';
    const XML_PATH_CUSTOMER_SESSION_TYPE = 'punchout2go_punchout/session/type';
    const XML_PATH_AUTO_CREATE_USER = 'punchout2go_punchout/customer/auto_create_user';
    const XML_PATH_CUSTOMER_ATTRIBUTE_MAP = 'punchout2go_punchout/customer/preinsert_customer_attribute_map';
    const XML_PATH_CREATE_ADDRESS = 'punchout2go_punchout/customer/address_to_cart';
    const XML_PATH_RETURN_LINK = 'punchout2go_punchout/display/return_link_enabled';
    const XML_PATH_RETURN_LINK_LABEL = 'punchout2go_punchout/display/return_link_label';
    const XML_PATH_IS_PUNCHOUT_ACTIVE = 'punchout2go_punchout/security/punchout_active';
    const XML_PATH_IS_MAINTAIN_QUERY_STRING = 'punchout2go_punchout/system/query_string';

    const DEFAULT_FIRSTNAME = 'Punchout User';
    const DEFAULT_LASTNAME = 'No Last Name';

    /**
     * @var Json
     */
    protected $jsonSerializer;

    /**
     * Data constructor.
     * @param Json $jsonSerializer
     * @param Context $context
     */
    public function __construct(Json $jsonSerializer, Context $context)
    {
        $this->jsonSerializer = $jsonSerializer;
        parent::__construct($context);
    }

    /**
     * @param null $store
     * @return bool
     */
    public function isLog($store = null)
    {
        return $this->scopeConfig->isSetFlag(
            static::XML_PATH_IS_LOG,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @param null $store
     * @return bool
     */
    public function isSessionValidate($store = null)
    {
        return $this->scopeConfig->isSetFlag(
            static::XML_PATH_IS_VALIDATE_SESSION,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @param null $store
     * @return mixed
     */
    public function getValidateSessionUrl($store = null)
    {
        return $this->scopeConfig->getValue(
            static::XML_PATH_VALIDATE_SESSION_URL,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @param null $store
     * @return bool
     */
    public function isSessionEncrypted($store = null)
    {
        return $this->scopeConfig->isSetFlag(
            static::XML_PATH_IS_SESSION_ENCRYPTED,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @param null $store
     * @return mixed
     */
    public function getEncryptionKey($store = null)
    {
        return $this->scopeConfig->getValue(
            static::XML_PATH_SESSION_ENCRYPTION_KEY,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @param null $store
     * @return mixed
     */
    public function getCustomerSessionType($store = null)
    {
        return $this->scopeConfig->getValue(
            static::XML_PATH_CUSTOMER_SESSION_TYPE,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @param null $store
     * @return bool
     */
    public function isAutoCreateUser($store = null)
    {
        return $this->scopeConfig->isSetFlag(
            static::XML_PATH_AUTO_CREATE_USER,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @param null $store
     * @return bool
     */
    public function isAddressToCart($store = null)
    {
        return $this->scopeConfig->isSetFlag(
            static::XML_PATH_CREATE_ADDRESS,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @param null $store
     * @return bool
     */
    public function isReturnEnabled($store = null)
    {
        return $this->scopeConfig->isSetFlag(
            static::XML_PATH_RETURN_LINK,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @param null $store
     * @return string
     */
    public function getReturnLinkLabel($store = null)
    {
        $value = (string) $this->scopeConfig->getValue(
            static::XML_PATH_RETURN_LINK_LABEL,
            ScopeInterface::SCOPE_STORE,
            $store
        );
        return strlen($value) ? $value : "Return To Procurement System";
    }

    /**
     * @param null $store
     * @return bool
     */
    public function isPunchoutActive($store = null)
    {
        return $this->scopeConfig->isSetFlag(
            static::XML_PATH_IS_PUNCHOUT_ACTIVE,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @param null $store
     * @return array
     */
    public function getCustomerAttributeMap($store = null)
    {
        $value = $this->scopeConfig->getValue(
            static::XML_PATH_CUSTOMER_ATTRIBUTE_MAP,
            ScopeInterface::SCOPE_STORE,
            $store
        );
        if (!strlen($value)) {
            return [];
        }
        return (array) $this->jsonSerializer->unserialize($value);
    }

    /**
     * @param string $sessionId
     * @return array|string|string[]|null
     */
    public function getRemoteInfoUrl(string $sessionId = '')
    {
        return preg_replace('/{pos}/', $sessionId, $this->getValidateSessionUrl());
    }

    /**
     * @param string $to
     * @return array
     */
    public function getAddressName(string $to)
    {
        $s = [];
        if (preg_match("/^(.+),(.+)$/", $to, $s)) {
            return [trim($s[1]), trim($s[2])];
        } else {
            $split = array_pad(explode(" ", $to), 2, '');
            list($first, $last) = $split;
        }
        return [$first, $last];
    }

    /**
     * @todo improve name splitting logic
     *
     * @param $data
     *
     * @return array
     */
    public function getUserSplitName($data)
    {
        $nameArray = $s = [];
        $name = $this->getNameFromContact($data) ?: $this->getNameFromShippingAddress($data);
        preg_match('/^(.+) ([^ ]+)$/', $name, $s);
        if (count($s) > 2) {
            $nameArray[] = $s[1];
            $nameArray[] = $s[2];
        } else {
            $nameArray[] = static::DEFAULT_FIRSTNAME;
            $nameArray[] = static::DEFAULT_LASTNAME;
        }

        return $nameArray;
    }

    /**
     * @param array $data
     * @return string
     */
    protected function getNameFromContact(array $data) : string
    {
        return $data['body']['contact']['name'] ?? '';
    }

    /**
     * @param array $data
     * @return string
     */
    protected function getNameFromShippingAddress(array $data) : string
    {
        return $data['body']['shipping']['shipping_to'] ?? '';
    }

    /**
     * @param string $lineId
     * @return false|string[]
     */
    public function getQuoteItemIdInfo(string $lineId)
    {
        return explode("/", $lineId);
    }

    /**
     * @param array $source
     * @return string[]
     */
    public function prepareSource(array $data)
    {
        $source = isset($data['source']) ? trim($data['source']) : '';
        $dest = isset($data['destination']) ? trim($data['destination']) : '';
        return [$source, $dest];
    }

    /**
     * @param $store
     * @return bool
     */
    public function isMaintainQueryString($store = null) {
        return $this->scopeConfig->isSetFlag(
            static::XML_PATH_IS_MAINTAIN_QUERY_STRING,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }
}
