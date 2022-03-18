<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Block\Account;

use Magento\Customer\Model\Url;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Framework\Data\Helper\PostHelper;
use Magento\Framework\View\Element\Template\Context as TemplateContext;
use Punchout2go\Punchout\Helper\Data;
use Punchout2go\Punchout\Model\Session as PUNSession;

/**
 * Customer authorization link
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 */
class AuthorizationLink extends \Magento\Framework\View\Element\Html\Link
{
    /**
     * Customer session
     *
     * @var \Magento\Framework\App\Http\Context
     */
    protected $httpContext;

    /**
     * @var \Magento\Customer\Model\Url
     */
    protected $customerUrl;

    /**
     * @var \Magento\Framework\Data\Helper\PostHelper
     */
    protected $postDataHelper;

    /**
     * @var \Punchout2go\Punchout\Helper\Data
     */
    protected $punchoutHelper;

    /**
     * @var \Punchout2go\Punchout\Model\Session
     */
    protected $punchoutSession;

    /**
     * AuthorizationLink constructor.
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\App\Http\Context              $httpContext
     * @param \Magento\Customer\Model\Url                      $customerUrl
     * @param \Magento\Framework\Data\Helper\PostHelper        $postDataHelper
     * @param \Punchout2go\Punchout\Helper\Data                $punchoutHelper
     * @param \Punchout2go\Punchout\Model\Session              $punchoutSession
     * @param array                                            $data
     */
    public function __construct(
        TemplateContext $context,
        HttpContext $httpContext,
        Url $customerUrl,
        PostHelper $postDataHelper,
        Data $punchoutHelper,
        PUNSession $punchoutSession,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->httpContext = $httpContext;
        $this->customerUrl = $customerUrl;
        $this->postDataHelper = $postDataHelper;
        $this->punchoutHelper = $punchoutHelper;
        $this->punchoutSession = $punchoutSession;
    }

    /**
     * Retrieve params for post request
     *
     * @return string
     */
    public function getPostParams()
    {
        return $this->postDataHelper->getPostData($this->getHref());
    }

    /**
     * @return string
     */
    public function getHref()
    {
        return $this->punchoutHelper->getUrl('punchout/session/close');
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return (string) $this->punchoutHelper->getConfig('punchout2go_punchout/display/return_link_label');
    }

    /**
     * Is logged in
     *
     * @return bool
     */
    public function isPunchoutSession()
    {
        return $this->punchoutSession->isPunchoutSession();
    }
}
