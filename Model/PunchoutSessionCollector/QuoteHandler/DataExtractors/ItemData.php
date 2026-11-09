<?php
declare(strict_types=1);

namespace Punchout2Go\Punchout\Model\PunchoutSessionCollector\QuoteHandler\DataExtractors;

use Punchout2Go\Punchout\Helper\Data;
use Punchout2Go\Punchout\Model\DataExtractorInterface;

/**
 * Class ItemDataExtractors
 * @package Punchout2Go\Punchout\Model\QuoteHandler\DataExtractors
 */
class ItemData implements DataExtractorInterface
{
    /**
     * @var Data
     */
    protected $helper;

    /**
     * @param Data $helper
     */
    public function __construct(Data $helper)
    {
        $this->helper = $helper;
    }

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
        $isEdit = in_array($params['operation'] ?? '', ['edit', 'inspect'], true);

        foreach ((array) $params["body"]['items'] as $item) {
            if (!isset($item['primaryId'])) {
                continue;
            }
            if ($this->helper->isSelectedItem($item)) {
                // The selected (Level 2 / catalog) item only picks the landing product page
                // on edit/inspect; adding it would duplicate or inflate the existing cart.
                if ($isEdit) {
                    continue;
                }
                // Its secondaryId is the catalog's aux id, never one of our references.
                $lineId = '';
            } else {
                $lineId = (string) ($item['secondaryId'] ?? '');
                if (!$this->helper->isQuoteItemReference($lineId)) {
                    $lineId = '';
                }
            }
            $result['items'][] = [
                'sku' => $item['primaryId'],
                'line_id' => $lineId,
                'qty' => $item['quantity'] ?? 1
            ];
        }
        return $result;
    }
}
