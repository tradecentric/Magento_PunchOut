<?php
declare(strict_types=1);

namespace Punchout2Go\Punchout\Model;

use Magento\Framework\Exception\LocalizedException;

/**
 * Class CustomerDataDataExtractor
 * @package Punchout2Go\Punchout\Model\CustomerHandler
 */
class DataExtractorPool implements DataExtractorInterface
{
    /**
     * @var array
     */
    protected $extractorPool = [];

    /**
     * CustomerDataDataExtractor constructor.
     * @param array $extractorPool
     */
    public function __construct(array $extractorPool)
    {
        $this->extractorPool = $extractorPool;
    }

    /**
     * @param array $data
     * @return array
     */
    public function extract(array $data) : array
    {
        $result = [];
        foreach ($this->extractorPool as $extractor) {
            if (!($extractor instanceof DataExtractorInterface)) {
                throw new LocalizedException(__(
                    'Class should be instance of Punchout2Go\Punchout\Model\CustomerHandler\ExtractorInterface'
                ));
            }
            $result = array_merge($result, $extractor->extract($data));
        }
        return $result;
    }
}
