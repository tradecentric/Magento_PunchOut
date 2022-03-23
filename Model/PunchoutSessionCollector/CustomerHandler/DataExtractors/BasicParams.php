<?php
declare(strict_types=1);

namespace Punchout2Go\Punchout\Model\PunchoutSessionCollector\CustomerHandler\DataExtractors;

use Punchout2Go\Punchout\Model\DataExtractorInterface;

/**
 * Interface DataExtractorInterface
 * @package Punchout2Go\Punchout\Model\CustomerHandler\Extractors
 */
class BasicParams implements DataExtractorInterface
{
    /**
     * @var \Punchout2Go\Punchout\Helper\Data
     */
    protected $helper;

    /**
     * BasicParamsDataExtractor constructor.
     * @param \Punchout2Go\Punchout\Helper\Data $helper
     */
    public function __construct(\Punchout2Go\Punchout\Helper\Data $helper)
    {
        $this->helper = $helper;
    }

    /**
     * @param array $params
     * @return array|string[]
     */
    public function extract(array $params): array
    {
        if (!isset($params['body']['contact'])) {
            return [];
        }
        // set email for log use
        $result = [];
        $result['email'] = trim($params['body']['contact']['email']);
        $nameArray = $this->helper->getUserSplitName($params);
        $result['firstname'] = $nameArray[0];
        $result['lastname'] = $nameArray[1];
        return $result;
    }
}
