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
        if ($this->isNotEmpty('UserFirstName', $bodyData)
            && $this->isNotEmpty('UserLastName', $bodyData)
        ) {
            $result['firstname'] = trim($bodyData['UserFirstName']);
            $result['lastname'] = trim($bodyData['UserLastName']);
        }
        return $result;
    }

    /**
     * @param string $fieldId
     * @param array $data
     * @return bool
     */
    protected function isNotEmpty(string $fieldId, array $data): bool
    {
        return isset($data[$fieldId]) && $data[$fieldId] !== "";
    }
}
