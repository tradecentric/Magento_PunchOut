<?php
declare(strict_types=1);

namespace Punchout2Go\Punchout\Model;

/**
 * Interface DataExtractorInterface
 * @package Punchout2Go\Punchout\Model\CustomerHandler\Extractors
 */
interface DataExtractorInterface
{
    /**
     * @param array $params
     * @return mixed[]
     */
    public function extract(array $params): array;
}
