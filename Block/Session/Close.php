<?php
namespace Punchout2go\Punchout\Block\Session;

use Punchout2go\Punchout\Cart as PUNCart;
use Punchout2go\Punchout\Model\Session as PUNSession;
use Punchout2go\Punchout\Helper\Data as PUNHelper;

/**
 * Close session link
 */
class Close extends \Magento\Framework\View\Element\Html\Link
{
    /** @var \Punchout2go\Punchout\Cart */
    protected $punchoutCart;
    /** @var \Punchout2go\Punchout\Model\Session */
    protected $punchoutSession;
    /** @var \Punchout2go\Punchout\Helper\Data */
    protected $helper;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Punchout2go\Punchout\Cart                       $punchoutCart
     * @param \Punchout2go\Punchout\Model\Session              $punchoutSession
     * @param \Punchout2go\Punchout\Helper\Data                $helper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        PUNCart $punchoutCart,
        PUNSession $punchoutSession,
        PUNHelper $helper,
        array $data = []
    ) {
        $this->helper = $helper;
        $this->punchoutCart = $punchoutCart;
        $this->punchoutSession = $punchoutSession;
        parent::__construct($context, $data);
    }

    /**
     * return the close link.
     *
     * @return string
     */
    public function getHref()
    {
        return $this->getUrl('punchout/session/close');
    }

    /**
     * return the string label.
     *
     * @return mixed|string
     */
    public function getLabel()
    {
        $label = trim($this->helper->getConfig('punchout2go_punchout/display/return_link_label'));
        if(strlen($label) == 0) $label = "Return To Procurement System";
        return $label;
    }

    public function getDisplayReturnLinkEnabled()
    {
        return $this->helper->getConfig('punchout2go_punchout/display/return_link_enabled');
    }

    public function isPunchoutSession()
    {
        return $this->punchoutSession->isPunchoutSession();
    }

    public function getPunchoutSession()
    {
        return $this->punchoutSession;
    }
}
