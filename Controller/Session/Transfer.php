<?php

namespace Punchout2go\Punchout\Controller\Session;

use Magento\Backend\Model\Session;
use Magento\Checkout\Model\Cart as MageCart;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context as ActionContext;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\View\Result\PageFactory;
use Punchout2go\Punchout\Cart as PUNCart;
use Punchout2go\Punchout\Helper\Data as HelperData;
use Punchout2go\Punchout\Cart\Distiller as CartDistiller;
use Punchout2go\Punchout\Model\Session as PUNSession;
use Magento\Quote\Api\CartRepositoryInterface as QuoteRepository;

class Transfer extends Action
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
    /** @var  \Magento\Quote\Api\CartRepositoryInterface */
    protected $quoteRepository;

    /**
     * @param \Magento\Framework\App\Action\Context              $context
     * @param \Punchout2go\Punchout\Model\Session                $punchoutSession
     * @param \Magento\Framework\View\Result\PageFactory         $resultPageFactory
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Checkout\Model\Cart                       $cart
     * @param \Punchout2go\Punchout\Cart                         $punchoutCart
     * @param \Punchout2go\Punchout\Helper\Data                  $dataHelper
     * @param \Punchout2go\Punchout\Cart\Distiller               $distiller
     * @param \Magento\Quote\Api\CartRepositoryInterface         $quoteRepository
     */
    public function __construct(
        ActionContext $context,
        PUNSession $punchoutSession,
        PageFactory $resultPageFactory,
        ScopeConfigInterface $scopeConfig,
        MageCart $cart,
        PUNCart $punchoutCart,
        HelperData $dataHelper,
        CartDistiller $distiller,
        QuoteRepository $quoteRepository
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->punchoutSession = $punchoutSession;
        $this->context = $context;
        $this->resultPageFactory = $resultPageFactory;
        $this->mageCart = $cart;
        $this->punchoutCart = $punchoutCart;
        $this->helper = $dataHelper;
        $this->distiller = $distiller;
        $this->quoteRepository = $quoteRepository;
        parent::__construct($context);
    }

    /**
     * Default punchout controller
     * @return \Magento\Framework\View\Result\Page $page
     */
    public function execute()
    {
        $headers = $this->helper->getHeaders();
        $punSessionId = $this->punchoutSession->getPunchoutSessionId();
        $responseObj = $this->getResponse();
        foreach ($headers as $header) {
            foreach ($header as $headerKey => $headerValue) {
                //header("{$headerKey}: {$headerValue}");
                $responseObj->setHeader($headerKey, $headerValue);
            }
        }
        /** @var \Punchout2go\Punchout\Cart $punchoutCart */
        $punchoutCart = $this->punchoutCart;
        $punchoutCart->setPunchoutSession($this->punchoutSession->getPunchoutSessionId());
        $punchoutCart->setPunchoutSessionId($this->punchoutSession->getPunchoutSessionId());
        $punchoutCart->setPunchoutReturnUrl($this->punchoutSession->getPunchoutReturnUrl());

        /** @var \Punchout2go\Punchout\Cart\Distiller $punchoutDistiller */
        $punchoutDistiller = $this->distiller;

        /** @var \Magento\Checkout\Model\Cart $cart */
        $cartObject = $this->mageCart;
        $quote = $cartObject->getQuote();

        $punchoutCart = $punchoutDistiller->buildPunchoutReturn($punchoutCart, $quote);

        /** @var \Punchout2go\Punchout\Block\Transfer $transferBlock */
        $transferBlock = $this->context->getView()->getLayout()->setIsPrivate(true)->createBlock(
            '\Punchout2go\Punchout\Block\Transfer',
            'punchout.transfer',
            ['template' => 'punchout_transfer.phtml', 'cacheable' => false]
        );

        $transferBlock->getCacheKey('punchout_transfer_' . $punSessionId . '_' . uniqid());
        $transferBlock->setTemplate('punchout_transfer.phtml');
        $transferBlock->setPunchoutSession($this->punchoutSession);
        $transferBlock->setMageCart($cartObject);
        $transferBlock->setPunchoutCart($punchoutCart);

        $transferBlock->getCacheKeyInfo();
        $content = $transferBlock->toHtml();
        $quote->setIsActive(0);
        $this->quoteRepository->save($quote);
        /** @var \Magento\Framework\App\Response\Http $responseObj */
        $responseObj = $this->getResponse();
        $responseObj->setContent($content);
        $responseObj->setNoCacheHeaders();

        // destroy the magento session data
        $this->punchoutSession->destroySession();
    }
}
