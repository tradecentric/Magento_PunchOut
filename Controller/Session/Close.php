<?php

namespace Punchout2go\Punchout\Controller\Session;

use http\Url;
use Magento\Backend\Model\Session;
use Magento\Checkout\Model\Cart as MageCart;
use Magento\Framework\Stdlib\CookieManagerInterface as CookieManager;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context as ActionContext;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\View\Result\PageFactory;
use Punchout2go\Punchout\Cart as PUNCart;
use Punchout2go\Punchout\Helper\Data as HelperData;
use Punchout2go\Punchout\Cart\Distiller as CartDistiller;
use Punchout2go\Punchout\Model\Session as PUNSession;

class Close extends Action
{
    /** @var \Magento\Framework\View\Result\PageFactory  */
    protected $resultPageFactory;
    /** @var  \Magento\Framework\App\Action\Context */
    protected $context;
    /** @var \Punchout2go\Punchout\Model\Session */
    protected $punchoutSession;
    /** @var \Magento\Framework\App\Config\ScopeConfigInterface */
    protected $scopeConfig;
    /** @var \Magento\Checkout\Model\Cart  */
    protected $mageCart;
    /** @var \Punchout2go\Punchout\Cart  */
    protected $punchoutCart;
    /** @var \Punchout2go\Punchout\Helper\Data $helper */
    protected $helper;
    /** @var \Punchout2go\Punchout\Cart\Distiller  */
    protected $distiller;

    /**
     * @param \Magento\Framework\App\Action\Context              $context
     * @param \Punchout2go\Punchout\Model\Session                $punchoutSession
     * @param \Magento\Framework\View\Result\PageFactory         $resultPageFactory
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Checkout\Model\Cart                       $cart
     * @param \Punchout2go\Punchout\Cart                         $punchoutCart
     * @param \Punchout2go\Punchout\Helper\Data                  $dataHelper
     * @param \Punchout2go\Punchout\Cart\Distiller               $distiller
     * @param \Magento\Framework\Stdlib\CookieManagerInterface   $cookieManager
     */
    public function __construct(
        ActionContext $context,
        PUNSession $punchoutSession,
        PageFactory $resultPageFactory,
        ScopeConfigInterface $scopeConfig,
        MageCart $cart,
        PUNCart $punchoutCart,
        HelperData $dataHelper,
        CartDistiller $distiller
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->punchoutSession = $punchoutSession;
        $this->context = $context;
        $this->resultPageFactory = $resultPageFactory;
        $this->mageCart = $cart;
        $this->punchoutCart = $punchoutCart;
        $this->helper = $dataHelper;
        $this->distiller = $distiller;
        parent::__construct($context);
    }

    /**
     * Default punchout controller
     * @return \Magento\Framework\View\Result\Page $page
     */
    public function execute()
    {
        $responseObj = $this->getResponse();
        $headers = $this->helper->getHeaders();
        foreach ($headers as $header) {
            foreach ($header as $headerKey => $headerValue) {
                $responseObj->setHeader($headerKey, $headerValue);
            }
        }

        $punchoutCart = $this->punchoutCart;
        $punchoutCart->setPunchoutSession($this->punchoutSession);
        $punchoutCart->setPunchoutSessionId($this->punchoutSession->getPunchoutSessionId());
        $punchoutCart->setPunchoutReturnUrl($this->punchoutSession->getPunchoutReturnUrl());
        /** @var \Punchout2go\Punchout\Block\Transfer $exit */
        $closeBlock = $this->context->getView()->getLayout()->setIsPrivate(true)->createBlock(
            '\Punchout2go\Punchout\Block\Close',
            'punchout.exit',
            ['template' => 'punchout_exit.phtml', 'cacheable' => false]
        );
        $closeBlock->setTemplate('punchout_exit.phtml');
        $closeBlock->getCacheKeyInfo();
        $content = $closeBlock->toHtml();
        // destroy the magento session data
        $this->helper->debug("Destroying Session!");
        /** @var \Magento\Framework\App\Response\Http $responseObj */
        $responseObj = $this->getResponse();
        $responseObj->setContent($content);
        $responseObj->setNoCacheHeaders();
        $this->punchoutSession->destroySession();
    }
}
