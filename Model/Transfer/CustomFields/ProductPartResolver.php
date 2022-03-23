<?php
declare(strict_types=1);

namespace Punchout2Go\Punchout\Model\Transfer\CustomFields;

use Magento\Framework\Exception\LocalizedException;

/**
 * Class PartFactory
 * @package Punchout2Go\Punchout\Model\Transfer\CustomFields
 */
class ProductPartResolver
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var array
     */
    protected $partObjects;

    /**
     * PartFactory constructor.
     * @param array $partResolvers
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        array $partObjects = []
    ) {
        $this->objectManager = $objectManager;
        $this->partObjects = $partObjects;
    }

    /**
     * @param string $partType
     * @return mixed
     * @throws LocalizedException
     */
    public function resolve($partType = '') : ?ProductPartInterface
    {
        if (!isset($this->partObjects[$partType])) {
            return null;
        }
        return $this->objectManager->create($this->partObjects[$partType]);
    }
}
