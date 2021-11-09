<?php
declare(strict_types=1);

namespace Punchout2Go\Punchout\Model\Request;

use Magento\Framework\Exception\LocalizedException;
use Punchout2Go\Punchout\Api\RequestParamsHandlerInterface;

/**
 * Class HandlePool
 * @package Punchout2Go\Punchout\Model\Request
 */
class HandlePool implements RequestParamsHandlerInterface
{
    /**
     * @var array RequestParamsHandlerInterface[]
     */
    protected $handlers = [];

    /**
     * HandlePool constructor.
     * @param array $handlers
     */
    public function __construct(array $handlers = [])
    {
        $this->handlers = $handlers;
    }

    /**
     * @param array $params
     * @return array
     */
    public function handle(array $params = []): array
    {
        foreach ($this->handlers as $handler) {
            if (!($handler instanceof RequestParamsHandlerInterface)) {
                throw new LocalizedException(__('Class type should be Punchout2Go\Punchout\Api\RequestParamsHandlerInterface'));
            }
            $params = $handler->handle($params);
        }
        return $params;
    }
}
