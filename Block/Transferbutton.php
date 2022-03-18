<?php

/**
 * Copyright © 2016 PunchOut2Go. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Punchout2go\Punchout\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

/**
 * One page checkout cart link
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Transferbutton extends Template
{
    protected $scopeConfig;

    public function __construct(Context $context, array $data = [])
    {
        $this->scopeConfig = $context->getScopeConfig();
        parent::__construct($context, $data);
    }

    /**
     * @return string
     */
    public function getTransferUrl()
    {
        return $this->getUrl('punchout/session/transfer');
    }

    /**
     * @return string
     */
    public function getTransferLabel()
    {
        return $this->scopeConfig->getValue('punchout2go_punchout/display/transfer_button_label');
    }

    /**
     * @return string
     */
    public function getTransferHelp()
    {
        return $this->scopeConfig->getValue('punchout2go_punchout/display/transfer_button_help');
    }

    /**
     * @return string
     */
    public function getTransferButtonCSS()
    {
        return $this->scopeConfig->getValue('punchout2go_punchout/display/transfer_button_css_class');
    }

    protected function _prepareLayout()
    {
        $this->setMessage('Hello again!');
    }
}
