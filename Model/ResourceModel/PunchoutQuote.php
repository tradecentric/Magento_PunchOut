<?php
declare(strict_types=1);

namespace Punchout2Go\Punchout\Model\ResourceModel;

/**
 * Class PunchoutQuote
 * @package Punchout2Go\Punchout\Model\ResourceModel
 */
class PunchoutQuote extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * @var \array[][]
     */
    protected $_serializableFields = ['params' => [[], []]];

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('punchout_quote', 'entity_id');
    }
}
