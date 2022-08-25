<?php
declare(strict_types=1);

namespace Punchout2Go\Punchout\Model\Transfer;

use Magento\Framework\Exception\LocalizedException;

/**
 * Class ItemDataResolver
 * @package Punchout2Go\Punchout\Model\Transfer
 */
class ItemDataPool
{
    /**
     * @var array
     */
    protected $objectMappings = [];

    /**
     * ItemDataPool constructor.
     * @param array $objectMappings
     */
    public function __construct(array $objectMappings = [])
    {
        $this->objectMappings = $objectMappings;
    }

    /**
     * @param $object
     * @param array $data
     * @return mixed
     * @throws LocalizedException
     */
    public function get($object, array $data = [])
    {
        foreach ($this->objectMappings as $type => $handler) {
            if (!($object instanceof $type)) {
                continue;
            }
            return $handler;
        }
        throw new LocalizedException(__('Item data resolver not found'));
    }
}
