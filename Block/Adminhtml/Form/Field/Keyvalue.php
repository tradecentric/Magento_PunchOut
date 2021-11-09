<?php
declare(strict_types=1);

namespace Punchout2Go\Punchout\Block\Adminhtml\Form\Field;

/**
 * Class Keyvalue
 * @package Punchout2Go\Punchout\Block\Adminhtml\Form\Field
 */
class Keyvalue extends \Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray
{
    /**
     * Prepare to render
     *
     * @return void
     */
    protected function _prepareToRender()
    {
        $this->addColumn('source', ['label' => __('Source'),'width' => 200]);
        $this->addColumn('destination', ['label' => __('Destination'),'width' => 200]);
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add Another');
    }

    /**
     * Prepare existing row data object
     *
     * @param \Magento\Framework\DataObject $row
     * @return void
     */
    protected function _prepareArrayRow(\Magento\Framework\DataObject $row)
    {
        $optionExtraAttr = [];
        $row->setData('option_extra_attrs', $optionExtraAttr);
    }
}
