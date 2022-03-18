<?php
/**
 * Front end class for showing the version number in the configuration for Punchout
 */

namespace Punchout2go\Punchout\Block\Forms\Config;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Punchout2go\Punchout\Helper\Data as Helper;

class Version extends Field
{
    protected $helper;

    public function __construct(
        \Magento\Backend\Block\Template\Context $templateContext,
        Helper $helper,
        array $data = []
    ) {
        $this->helper = $helper;
        parent::__construct($templateContext, $data);
    }

    /**
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        return __(
            '<label class="label"><span>' . $this->helper->getModuleVersion() . '</span></label>'
        );
    }
}
