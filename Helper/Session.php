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
    const XML_PATH_IS_PUNCHOUT_ONLY = 'punchout2go_punchout/site/punchout_only';
    const XML_PATH_IS_PUNCHOUT_ONLY_URL = 'punchout2go_punchout/site/punchout_only_url';

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
    public function isPunchoutOnly($store = null)
    {
        return (string) $this->scopeConfig->isSetFlag(
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
}