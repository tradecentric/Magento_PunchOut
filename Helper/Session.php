<?php
declare(strict_types=1);

namespace Punchout2Go\Punchout\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;

/**
 * Class Transfer
 * @package Punchout2Go\Punchout\Helper
 */
class Session extends \Magento\Framework\App\Helper\AbstractHelper
{
    const FIRST_LOAD = 'fl';
    const XML_PATH_REQUIRED_ELEMENTS_URL = 'punchout2go_punchout/session/required_elements';
    const XML_PATH_RELOAD_JS_SECTIONS = 'punchout2go_punchout/session/js_reload_sections';
    const XML_PATH_START_URL = 'punchout2go_punchout/session/start_redirect_new';
    const XML_PATH_START_URL_EDIT = 'punchout2go_punchout/session/start_redirect_edit';
    const XML_PATH_START_URL_ITEM_EDIT = 'punchout2go_punchout/session/start_redirect_item';
    const XML_PATH_IS_PUNCHOUT_ONLY = 'punchout2go_punchout/site/punchout_only';
    const XML_PATH_IS_PUNCHOUT_ONLY_URL = 'punchout2go_punchout/site/punchout_only_url';
    const XML_PATH_EXCLUDE_POS_ID_IN_REDIRECT = 'punchout2go_punchout/session/exclude_posid_redirect';
    const XML_PATH_IGNORE_ITEMS = 'punchout2go_punchout/session/selected_item_ignore';
    
    /**
     * @param null $store
     * @return string
     */
    public function getJsReloadSections($store = null)
    {
        return (string) $this->scopeConfig->getValue(
            static::XML_PATH_RELOAD_JS_SECTIONS,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @param null $store
     * @return false|string|string[]
     */
    public function getPunchoutRequiredElementsUrl($store = null)
    {
        $values = (string) $this->scopeConfig->getValue(
            static::XML_PATH_REQUIRED_ELEMENTS_URL,
            ScopeInterface::SCOPE_STORE,
            $store
        );
        if (strlen($values)) {
            return explode(',', $values);
        }
        return '';
    }

    /**
     * @param null $store
     * @return string
     */
    public function getSessionStartupUrl($store = null)
    {
        return (string) $this->scopeConfig->getValue(
            static::XML_PATH_START_URL,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @param null $store
     * @return string
     */
    public function getSessionStartupEditUrl($store = null)
    {
        return (string) $this->scopeConfig->getValue(
            static::XML_PATH_START_URL_EDIT,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @param null $store
     * @return string
     */
    public function getSessionStartupEditItemUrl($store = null)
    {
        return (string) $this->scopeConfig->getValue(
            static::XML_PATH_START_URL_ITEM_EDIT,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @param null $store
     * @return bool
     */
    public function isPunchoutOnly($store = null)
    {
        return $this->scopeConfig->isSetFlag(
            static::XML_PATH_IS_PUNCHOUT_ONLY,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @param null $store
     * @return string
     */
    public function getPunchoutOnlyRedirectLink($store = null)
    {
        return trim ((string) $this->scopeConfig->getValue(
            static::XML_PATH_IS_PUNCHOUT_ONLY_URL,
            ScopeInterface::SCOPE_STORE,
            $store
        ), '/');
    }

    /**
     * @param null $store
     * @return string
     */
    public function getPunchoutRedirectUrl($store = null)
    {
        return $this->_getUrl($this->getPunchoutOnlyRedirectLink($store));
    }

    /**
     * @return string
     */
    public function getFirstLoadParam()
    {
        return static::FIRST_LOAD;
    }

    /**
     * @param null $store
     * @return bool
     */
    public function isIncludePosidInRedirect($store = null)
    {
        return !$this->scopeConfig->isSetFlag(
            static::XML_PATH_EXCLUDE_POS_ID_IN_REDIRECT,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @param null $store
     * @return array
     */
    public function getIgnoreItems($store = null): array
    {
        return array_map(
            'trim', 
            explode(',', 
                (string) $this->scopeConfig->getValue(
                static::XML_PATH_IGNORE_ITEMS,
            ScopeInterface::SCOPE_STORE,
                    $store
                )
            )
        );
    }
}
