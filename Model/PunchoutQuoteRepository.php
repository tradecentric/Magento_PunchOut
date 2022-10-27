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
        if (isset($this->instances[$entityId])) {
            return $this->instances[$entityId];
        }
        /** @var \Punchout2Go\Punchout\Api\Data\PunchoutQuoteInterface $entity */
        $entity = $this->factory->create();
        $this->resource->load($entity, $entityId);
        if (!$entity->getId()) {
            throw NoSuchEntityException::singleField('id', $entityId);
        }
        $this->instances[$entityId] = $entity;
        $this->instancesQId[$entity->getQuoteId()] = $entity;
        $this->instancesPId[$entity->getPunchoutSessionId()] = $entity;
        return $entity;
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
        /** @var \Punchout2Go\Punchout\Api\Data\PunchoutQuoteInterface $entity */
        $entity = $this->factory->create();
        $this->resource->load($entity, $quoteId, PunchoutQuoteInterface::QUOTE_ID);
        if (!$entity->getId()) {
            throw NoSuchEntityException::singleField(PunchoutQuoteInterface::QUOTE_ID, $quoteId);
        }
        $this->instances[$entity->getId()] = $entity;
        $this->instancesQId[$entity->getQuoteId()] = $entity;
        $this->instancesPId[$entity->getPunchoutSessionId()] = $entity;
        return $entity;
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
        /** @var \Punchout2Go\Punchout\Api\Data\PunchoutQuoteInterface $entity */
        $entity = $this->factory->create();
        $this->resource->load($entity, $punchoutId, PunchoutQuoteInterface::PUNCHOUT_QUOTE_ID);
        if (!$entity->getId()) {
            throw NoSuchEntityException::singleField(PunchoutQuoteInterface::PUNCHOUT_QUOTE_ID, $punchoutId);
        }
        $this->instances[$entity->getId()] = $entity;
        $this->instancesQId[$entity->getQuoteId()] = $entity;
        $this->instancesPId[$entity->getPunchoutSessionId()] = $entity;
        return $entity;
    }

    /**
     * @param PunchoutQuoteInterface $punchoutQuote
     * @return PunchoutQuoteInterface
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    public function save(PunchoutQuoteInterface $punchoutQuote) : PunchoutQuoteInterface
    {
        $this->resource->save($punchoutQuote);
        $this->instances[$punchoutQuote->getId()] = $punchoutQuote;
        $this->instancesQId[$punchoutQuote->getQuoteId()] = $punchoutQuote;
        $this->instancesPId[$punchoutQuote->getPunchoutSessionId()] = $punchoutQuote;
        return $punchoutQuote;
    }

    /**
     * @param PunchoutQuoteInterface $punchoutQuote
     * @throws CouldNotDeleteException
     */
    public function delete(PunchoutQuoteInterface $punchoutQuote) : bool
    {
        $entityId = $punchoutQuote->getId();
        try {
            unset(
                $this->instances[$entityId],
                $this->instancesQId[$punchoutQuote->getQuoteId()],
                $this->instancesPId[$punchoutQuote->getPunchoutSessionId()]
            );
            $this->resource->delete($punchoutQuote);
        } catch (\Exception $e) {
            throw new CouldNotDeleteException(
                __(
                    'Cannot delete punchout quote with id %1',
                    $entityId
                ),
                $e
            );
        }
        return true;
    }

    /**
     * @param int $entityId
     * @return bool
     * @throws CouldNotDeleteException
     * @throws NoSuchEntityException
     */
    public function deleteById(int $entityId) : bool
    {
        $entity = $this->get($entityId);
        return $this->delete($entity);
    }
}
