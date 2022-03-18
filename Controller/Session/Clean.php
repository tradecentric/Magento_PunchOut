<?php

namespace Punchout2go\Punchout\Controller\Session;

use Magento\Checkout\Model\Cart as MageCart;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context as ActionContext;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Punchout2go\Punchout\Helper\Data as HelperData;
use Punchout2go\Punchout\Model\Session as PUNSession;

class Clean extends Action
{
    /** @var  \Magento\Framework\App\Action\Context */
    protected $context;
    /** @var \Punchout2go\Punchout\Model\Session */
    protected $scopeConfig;
    /** @var \Magento\Checkout\Model\Cart  */
    protected $mageCart;
    /** @var \Punchout2go\Punchout\Cart  */
    protected $helper;
    /** @var \Magento\Framework\Controller\Result\JsonFactory */
    protected $resultJsonFactory;

    /**
     * @param \Magento\Framework\App\Action\Context              $context
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Checkout\Model\Cart                       $cart
     * @param \Punchout2go\Punchout\Helper\Data                  $dataHelper
     */
    public function __construct(
        ActionContext $context,
        ScopeConfigInterface $scopeConfig,
        MageCart $cart,
        HelperData $dataHelper,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->context = $context;
        $this->mageCart = $cart;
        $this->helper = $dataHelper;
        $this->resultJsonFactory = $resultJsonFactory;
        parent::__construct($context);
    }

    /**
     * Default punchout controller
     * @return \Magento\Framework\View\Result\Page $page
     */
    public function execute()
    {

        /** @var \Magento\Checkout\Model\Cart $cart */
        $cartObject = $this->mageCart;

        // possibly iterate and re-set qty.
        $items = $cartObject->getItems();
        /** @var Magento\Quote\Model\Quote\Item $item */
        $updateData = [];
        foreach ($items as $item) {
            $updateData[$item->getId()] = ['qty'=>$item->getQty()];
        }
        $cartObject->updateItems($updateData);
        // re-save cart forcing re-collection.
        $cartObject->save();

        $result = $this->resultJsonFactory->create();
        return $result->setData($cartObject->getQuote()->getData());
    }
}
