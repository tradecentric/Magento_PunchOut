<?php

namespace Punchout2go\Punchout\Plugin;

/**
 * Class SessionStartControllerPlugin
 * @package Punchout2go\Punchout\Plugin
 */
class SessionStartControllerPlugin
{
    /**
     * @var \Punchout2go\Punchout\Helper\Config
     */
    private $helper;

    /**
     * @var EmailContext
     */
    private $emailContext;

    /**
     * SessionStartControllerPlugin constructor.
     * @param \Punchout2go\Punchout\Helper\Config $config
     * @param EmailContext $emailContext
     */
    public function __construct(
        \Punchout2go\Punchout\Helper\Data $config,
        \Punchout2go\Punchout\Plugin\EmailContext $emailContext
    ) {
        $this->helper = $config;
        $this->emailContext = $emailContext;
    }

    /**
     * @return array
     */
    public function beforeExecute()
    {
        $isNotSendEmail = $this->helper->getConfigFlag('punchout2go_punchout/site/punchout_not_send_email');
        $this->emailContext->setSendEmail(!$isNotSendEmail);
        return;
    }
}