<?php
declare(strict_types=1);

namespace Punchout2Go\Punchout\Model\PunchoutSessionCollector\CustomerHandler\DataExtractors;

use Punchout2Go\Punchout\Model\DataExtractorInterface;

/**
 * Interface DataExtractorInterface
 * @package Punchout2Go\Punchout\Model\CustomerHandler\Extractors
 */
class DefaultParams implements DataExtractorInterface
{
    /**
     * @param array $params
     * @return mixed[][]|\string[][]
     */
    public function extract(array $params): array
    {
        $custom = isset($params['custom']) ? $params['custom'] : [];
        return [
            'default_user' => $custom['default_user'] ?? '',
            'default_group' => $custom['default_group'] ?? ''
        ];
    }
}
