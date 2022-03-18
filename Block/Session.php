<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Punchout2go\Punchout\Block;

use Magento\Checkout\Model\Cart as MageCart;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context as TemplateContext;
use Punchout2go\Punchout\Cart as PUNCart;
use Punchout2go\Punchout\Helper\Data;
use Punchout2go\Punchout\Model\Session as PUNSession;

class Session extends Template
{

    /** @var \Punchout2go\Punchout\Cart */
    protected $punchoutCart;
    /** @var \Punchout2go\Punchout\Model\Session */
    protected $punchoutSession;
    /** @var \Magento\Checkout\Model\Cart */
    protected $mageCart;
    /** @var \Punchout2go\Punchout\Helper\Data */
    protected $helper;

    /**
     * Session constructor.
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Punchout2go\Punchout\Cart                       $punchoutCart
     * @param \Punchout2go\Punchout\Model\Session              $punchoutSession
     * @param \Magento\Checkout\Model\Cart                     $mageCart
     * @param \Punchout2go\Punchout\Helper\Data                $helper
     * @param array                                            $data
     */
    public function __construct(
        TemplateContext $context,
        PUNCart $punchoutCart,
        PUNSession $punchoutSession,
        MageCart $mCart,
        Data $helper,
        array $data = []
    ) {
        $this->helper = $helper;
        $this->punchoutCart = $punchoutCart;
        $this->punchoutSession = $punchoutSession;
        $this->mageCart = $mCart;
        parent::__construct($context, $data);
    }

    /**
     * @return \Magento\Checkout\Model\Cart
     */
    public function getMageCart()
    {
        return $this->mageCart;
    }

    /**
     * @param \Magento\Checkout\Model\Cart $mage_cart
     */
    public function setMageCart(MageCart $mage_cart)
    {
        $this->mageCart = $mage_cart;
    }

    /**
     * @return \Punchout2go\Punchout\Cart
     */
    public function getPunchoutCart()
    {
        return $this->punchoutCart;
    }

    /**
     * @param \Punchout2go\Punchout\Cart $punchout_cart
     */
    public function setPunchoutCart(PUNCart $punchout_cart)
    {
        $this->punchoutCart = $punchout_cart;
    }

    /**
     * @return \Punchout2go\Punchout\Model\Session
     */
    public function getPunchoutSession()
    {
        return $this->punchoutSession;
    }

    /**
     * @param \Punchout2go\Punchout\Model\Session $punchout_session
     */
    public function setPunchoutSession(PUNSession $punchout_session)
    {
        $this->punchoutSession = $punchout_session;
    }

    /**
     *
     */
    public function getConfigJson()
    {
        $array = $this->helper->getConfigData();
        $array['baseUrl'] = $this->getBaseUrl();
        return json_encode($array);
    }

    /**
     * @return \Punchout2go\Punchout\Helper\Data
     */
    public function getHelper()
    {
        return $this->helper;
    }
}
