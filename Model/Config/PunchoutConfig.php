<?php
declare(strict_types=1);

namespace Punchout2Go\Punchout\Model\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class PunchoutConfig
{
    public const XML_PATH_PUNCHOUT_ONLY = 'punchout2go_punchout/site/punchout_only';
    public const XML_PATH_HTTP_STATUS_CODE = 'punchout2go_punchout/site/punchout_only_http_status_code';
	public const XML_PATH_PUNCHOUT_ONLY_PAGE = 'punchout2go_punchout/site/punchout_only_page';
    public const XML_PATH_PUNCHOUT_ONLY_MESSAGE = 'punchout2go_punchout/site/punchout_only_message';

    private ScopeConfigInterface $scopeConfig;

    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Determine whether PunchOut-only mode is enabled for the given store.
     */
    public function isPunchoutOnly(?int $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_PUNCHOUT_ONLY,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Determine whether PunchOut-only mode is enabled for the given store.
     */
    public function isPunchoutOnlyPage(?int $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_PUNCHOUT_ONLY_PAGE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
	
    /**
     * Retrieve the PunchOut-only hard-fail message for the given store.
     */
    public function getPunchoutOnlyMessage(?int $storeId = null): string
    {
        $value = $this->scopeConfig->getValue(
            self::XML_PATH_PUNCHOUT_ONLY_MESSAGE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );

        if (is_string($value) && trim($value) !== '') {
            return $value;
        }

        return (string)__(
            'This storefront is available only through a PunchOut session.'
        );
    }
    public function getHttpStatusCode(?int $storeId = null): int
    {
        $value = (int)$this->scopeConfig->getValue(
            self::XML_PATH_HTTP_STATUS_CODE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );

        return in_array($value, [401, 403, 503], true)
            ? $value : 403;
    }
}