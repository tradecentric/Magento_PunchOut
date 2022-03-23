<?php
declare(strict_types=1);

namespace Punchout2Go\Punchout\Model\PunchoutSessionCollector\CustomerHandler;

/**
 * Class GroupByCodeProvider
 * @package Punchout2Go\Punchout\Model\CustomerHandler
 */
class GroupByCodeProvider
{
    /**
     * @var \Magento\Customer\Api\GroupRepositoryInterface
     */
    protected $groupRepository;

    /**
     * @var \Magento\Framework\Api\SimpleBuilderInterface
     */
    protected $builder;

    /**
     * GroupByCodeProvider constructor.
     * @param \Magento\Customer\Api\GroupRepositoryInterface $groupRepository
     * @param \Magento\Framework\Api\SimpleBuilderInterface $builder
     */
    public function __construct(
        \Magento\Customer\Api\GroupRepositoryInterface $groupRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $builder
    ) {
        $this->groupRepository = $groupRepository;
        $this->builder = $builder;
    }

    /**
     * @param string $groupCode
     * @return false|\Magento\Customer\Api\Data\GroupInterface|null
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getGroupByCode(string $groupCode)
    {
        $this->builder->addFilter('customer_group_code', $groupCode);
        $this->builder->setPageSize(1);
        $result = $this->groupRepository->getList($this->builder->create());
        $items = $result->getItems();
        if ($items) {
            return array_pop($items);
        }
        return false;
    }
}
