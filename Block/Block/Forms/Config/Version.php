<?php
declare(strict_types=1);

namespace Punchout2Go\Punchout\Block\Forms\Config;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Punchout2Go\Punchout\Model\ModuleVersion;

/**
 * Class Version
 * @package Punchout2Go\Punchout\Block\Forms\Config
 */
class Version extends Field
{
    /**
     * @var ModuleVersion
     */
    protected $moduleVersion;

    /**
     * Version constructor.
     * @param \Magento\Backend\Block\Template\Context $templateContext
     * @param ModuleVersion $moduleVersion
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $templateContext,
        ModuleVersion $moduleVersion,
        array $data = []
    ) {
        $this->moduleVersion = $moduleVersion;
        parent::__construct($templateContext, $data);
    }

    /**
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        return __(
            '<label class="label"><span>' . $this->moduleVersion->getModuleVersion('Punchout2Go_Punchout') . '</span></label>'
        );
    }
}
