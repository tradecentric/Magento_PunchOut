<?php
declare(strict_types=1);

namespace Punchout2Go\Punchout\Model\PunchoutSessionCollector\QuoteHandler\DataExtractors;

use Punchout2Go\Punchout\Model\DataExtractorInterface;

/**
 * Class ItemDataExtractors
 * @package Punchout2Go\Punchout\Model\QuoteHandler\DataExtractors
 */
class ItemData implements DataExtractorInterface
{
    /**
     * @param array $params
     * @return mixed[]|array[]
     */
    public function extract(array $params): array
    {
        if (!isset($params["body"]['items'])) {
            return [];
        }
        $result = ['items' => []];
        foreach ((array) $params["body"]['items'] as $item) {
            if (!isset($item['primaryId'])) {
                continue;
            }
            $result['items'][$item['primaryId']] = [
                'sku' => $item['primaryId'],
                'line_id' =>  $item['secondaryId'] ?? '',
                'qty' => $item['quantity'] ?? 1
            ];
        }
        return $result;
    }
}
