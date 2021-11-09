<?php
declare(strict_types=1);

namespace Punchout2Go\Punchout\Model\PunchoutSessionCollector\CustomerHandler\DataExtractors;

use Punchout2Go\Punchout\Model\DataExtractorInterface;

/**
 * Interface DataExtractorInterface
 * @package Punchout2Go\Punchout\Model\CustomerHandler\Extractors
 */
class Name implements DataExtractorInterface
{
    /**
     * @param array $params
     * @return array
     */
    public function extract(array $params): array
    {
        $result = [];
        if (!isset($params['body']['data'])) {
            return $result;
        }
        $bodyData = $params['body']['data'];
        if (isset($bodyData['UserFirstName']) && isset($bodyData['UserLastName'])) {
            $result['firstname'] = trim($bodyData['UserFirstName']);
            $result['lastname'] = trim($bodyData['UserLastName']);
        }
        return $result;
    }
}
