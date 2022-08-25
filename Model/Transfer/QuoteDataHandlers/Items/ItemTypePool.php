<?php
declare(strict_types=1);

namespace Punchout2Go\Punchout\Model\Transfer\QuoteDataHandlers\Items;

use Magento\Framework\Exception\LocalizedException;
use Punchout2Go\Punchout\Api\Data\ItemTransferDtoInterface;
use Punchout2Go\Punchout\Api\TransferCartItemDataInterface;

/**
 * Class ItemTypeFactory
 * @package Punchout2Go\Punchout\Model\Transfer\QuoteDataHandlers\Items
 */
class ItemTypePool
{
    /**
     * @var array
     */
    protected $resolvers = [];

    /**
     * @var array
     */
    protected $instances = [];

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**ram array $resolvers
     */
    public function __construct(array $resolvers = [])
    {
        ksort($resolvers);
        $this->resolvers = $resolvers;
    }

    /**
     * @param ItemTransferDtoInterface $dto
     * @return TransferCartItemDataInterface
     * @throws LocalizedException
     */
    public function get(ItemTransferDtoInterface $dto) : TransferCartItemDataInterface
    {
        foreach ($this->resolvers as $resolver) {
            if ($resolver->supports($dto)) {
                return $resolver;
            }
        }
        throw new LocalizedException(__('Item Type Resolver doesnt exist'));
    }
}
