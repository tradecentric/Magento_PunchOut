<?php
declare(strict_types=1);

namespace Punchout2Go\Punchout\Model;

use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\NoSuchEntityException;
use Punchout2Go\Punchout\Api\Data\PunchoutQuoteInterface;
use Punchout2Go\Punchout\Api\Data\PunchoutQuoteInterfaceFactory;
use Punchout2Go\Punchout\Api\PunchoutQuoteRepositoryInterface;

/**
 * Class PunchoutQuoteRepository
 * @package Punchout2Go\Punchout\Model
 */
class PunchoutQuoteRepository implements PunchoutQuoteRepositoryInterface
{
    /**
     * @var array
     */
    protected $instances = [];

    /**
     * @var array
     */
    protected $instancesQId = [];

    /**
     * @var array
     */
    protected $instancesPId = [];

    /**
     * @var ResourceModel\PunchoutQuote
     */
    protected $resource;

    /**
     * @var PunchoutQuoteInterfaceFactory
     */
    protected $factory;

    /**
     * PunchoutQuoteRepository constructor.
     * @param ResourceModel\PunchoutQuote $resource
     * @param PunchoutQuoteInterfaceFactory $factory
     */
    public function __construct(
        ResourceModel\PunchoutQuote $resource,
        PunchoutQuoteInterfaceFactory $factory
    ) {
        $this->resource = $resource;
        $this->factory = $factory;
    }

    /**
     * @param int $entityId
     * @return PunchoutQuoteInterface
     * @throws NoSuchEntityException
     */
    public function get(int $entityId): PunchoutQuoteInterface
    {
        if (isset($this->instances[$itemId])) {
            return $this->instances[$itemId];
        }
        /** @var \Punchout2Go\Punchout\Api\Data\PunchoutQuoteInterface $item */
        $item = $this->factory->create();
        $this->resource->load($item, $itemId);
        if (!$item->getId()) {
            throw NoSuchEntityException::singleField('id', $itemId);
        }
        $this->instances[$itemId] = $item;
        $this->instancesQId[$item->getQuoteId()] = $item;
        $this->instancesPId[$item->getPunchoutSessionId()] = $item;
        return $item;
    }

    /**
     * @param int $quoteId
     * @return PunchoutQuoteInterface
     * @throws NoSuchEntityException
     */
    public function getByQuoteId(int $quoteId): PunchoutQuoteInterface
    {
        if (isset($this->instancesQId[$quoteId])) {
            return $this->instances[$quoteId];
        }
        /** @var \Punchout2Go\Punchout\Api\Data\PunchoutQuoteInterface $item */
        $item = $this->factory->create();
        $this->resource->load($item, $quoteId, PunchoutQuoteInterface::QUOTE_ID);
        if (!$item->getId()) {
            throw NoSuchEntityException::singleField(PunchoutQuoteInterface::QUOTE_ID, $quoteId);
        }
        $this->instances[$item->getId()] = $item;
        $this->instancesQId[$item->getQuoteId()] = $item;
        $this->instancesPId[$item->getPunchoutSessionId()] = $item;
        return $item;
    }

    /**
     * @param string $punchoutId
     * @return PunchoutQuoteInterface
     * @throws NoSuchEntityException
     */
    public function getByPunchoutId(string $punchoutId): PunchoutQuoteInterface
    {
        if (isset($this->instancesPId[$punchoutId])) {
            return $this->instancesPId[$punchoutId];
        }
        /** @var \Punchout2Go\Punchout\Api\Data\PunchoutQuoteInterface $item */
        $item = $this->factory->create();
        $this->resource->load($item, $punchoutId, PunchoutQuoteInterface::PUNCHOUT_QUOTE_ID);
        if (!$item->getId()) {
            throw NoSuchEntityException::singleField(PunchoutQuoteInterface::PUNCHOUT_QUOTE_ID, $punchoutId);
        }
        $this->instances[$item->getId()] = $item;
        $this->instancesQId[$item->getQuoteId()] = $item;
        $this->instancesPId[$item->getPunchoutSessionId()] = $item;
        return $item;
    }

    /**
     * @param PunchoutQuoteInterface $punchoutQuote
     * @return PunchoutQuoteInterface
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    public function save(PunchoutQuoteInterface $punchoutQuote) : PunchoutQuoteInterface
    {
        unset($this->instances[$punchoutQuote->getId()]);
        $this->resource->save($punchoutQuote);
        return $punchoutQuote;
    }

    /**
     * @param PunchoutQuoteInterface $punchoutQuote
     * @throws CouldNotDeleteException
     */
    public function delete(PunchoutQuoteInterface $punchoutQuote) : bool
    {
        $itemId = $punchoutQuote->getId();
        try {
            unset($this->instances[$itemId]);
            unset($this->instancesQId[$punchoutQuote->getQuoteId()]);
            unset($this->instancesPId[$punchoutQuote->getPunchoutSessionId()]);
            $this->resource->delete($punchoutQuote);
        } catch (\Exception $e) {
            throw new CouldNotDeleteException(
                __(
                    'Cannot delete Order Item with id %1',
                    $itemId
                ),
                $e
            );
        }
        return true;
    }

    /**
     * @param int $itemId
     * @return bool
     * @throws CouldNotDeleteException
     * @throws NoSuchEntityException
     */
    public function deleteById(int $entityId) : bool
    {
        $item = $this->get($itemId);
        return $this->delete($item);
    }
}
