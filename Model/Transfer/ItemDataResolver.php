<?php
declare(strict_types=1);

namespace Punchout2Go\Punchout\Model\Transfer;

use Magento\Framework\Exception\LocalizedException;

/**
 * Class ItemDataResolver
 * @package Punchout2Go\Punchout\Model\Transfer
 */
class ItemDataResolver
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var array
     */
    protected $objectMappings = [];

    /**
     * ItemDataResolver constructor.
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param array $objectMappings
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        array $objectMappings = []
    ) {
        $this->objectManager = $objectManager;
        $this->objectMappings = $objectMappings;
    }

    /**
     * @param $object
     * @param array $data
     * @return mixed
     * @throws LocalizedException
     */
    public function resolve($object, array $data = [])
    {
        foreach ($this->objectMappings as $type => $handler) {
            if ($object instanceof $type) {
                return $this->objectManager->create($handler, $data);
            }
        }
        throw new LocalizedException(__('Item data resolver not found'));
    }
}
