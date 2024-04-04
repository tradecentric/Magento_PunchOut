<?php
declare(strict_types=1);

namespace Punchout2Go\Punchout\Model\Request\Handle;

use Magento\Framework\Exception\LocalizedException;
use Punchout2Go\Punchout\Api\RequestParamsHandlerInterface;
use Punchout2Go\Punchout\Model\Session;

/**
 * Class JsonHandler
 * @package Punchout2Go\Punchout\Model\Request
 */
class JsonHandler implements RequestParamsHandlerInterface
{
    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    protected $jsonSerializer;

    /**
     * JsonHandler constructor.
     * @param \Magento\Framework\Serialize\Serializer\Json $jsonSerializer
     */
    public function __construct(\Magento\Framework\Serialize\Serializer\Json $jsonSerializer)
    {
        $this->jsonSerializer = $jsonSerializer;
    }

    /**
     * @param array $params
     * @return mixed[]
     * @throws LocalizedException
     */
    public function handle(array $params = []): array
    {
        try {
            $params[Session::PARAMS] = $this->jsonSerializer->unserialize($params[Session::PARAMS]);
        } catch (\InvalidArgumentException $e) {
            throw new LocalizedException(__('PunchOut session params is not a valid JSON string'));
        }
        return $params;
    }
}
