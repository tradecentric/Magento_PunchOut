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

class Close extends Template
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
     * Transfer constructor.
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
        MageCart $mageCart,
        Data $helper,
        array $data = []
    ) {
        $this->helper = $helper;
        $this->punchoutCart = $punchoutCart;
        $this->punchoutSession = $punchoutSession;
        $this->mageCart = $mageCart;
        parent::__construct($context, $data);
    }

    /**
     * @return \Punchout2go\Punchout\Helper\Data
     */
    public function getHelper()
    {
        return $this->helper;
    }

    /**
     * @return \Punchout2go\Punchout\Cart
     */
    public function getPunchoutCart()
    {
        return $this->punchoutCart;
    }
}
