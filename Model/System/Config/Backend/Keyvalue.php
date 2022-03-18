<?php
namespace Punchout2go\Punchout\Model\System\Config\Backend;

/**
 * Backend for serialized array data
 */
class Keyvalue extends \Magento\Framework\App\Config\Value
{

    /**
     * Process data after load
     *
     * @return void
     */
    protected function _afterLoad()
    {
        $value = $this->getValue();
        $value = json_decode($value, true);
        if (isset($value['__empty'])) {
            unset($value['__empty']);
        }
        $this->setValue($value);
    }

    /**
     * Prepare data before save
     *
     * @return void
     */
    public function beforeSave()
    {
        $value = $this->getValue();
        if (isset($value['__empty'])) {
            unset($value['__empty']);
        }
        $value = json_encode($value);
        $this->setValue($value);
    }
}
