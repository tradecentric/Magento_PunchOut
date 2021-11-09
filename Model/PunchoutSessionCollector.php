<?php
declare(strict_types=1);

namespace Punchout2Go\Punchout\Model;

use Magento\Framework\Exception\LocalizedException;
use Punchout2Go\Punchout\Api\EntityHandlerInterface;
use Punchout2Go\Punchout\Api\SessionContainerInterface;
use Punchout2Go\Punchout\Api\SessionInterface;

/**
 * Class PunchoutSessionCollector
 * @package Punchout2Go\Punchout\Model
 */
class PunchoutSessionCollector implements EntityHandlerInterface
{
    protected $collectHandlers = [];

    /**
     * PunchoutSessionCollector constructor.
     * @param array $collectHandlers
     */
    public function __construct(array $collectHandlers = [])
    {
        $this->collectHandlers = $collectHandlers;
    }

    /**
     * @param SessionContainerInterface $object
     * @return mixed|void
     * @throws LocalizedException
     */
    public function handle(SessionContainerInterface $object)
    {
        foreach ($this->collectHandlers as $handler) {
            if (!($handler instanceof EntityHandlerInterface)) {
                throw new LocalizedException(__('Class type should be Punchout2Go\Punchout\Api\EntityHandlerInterface'));
            }
            $handler->handle($object);
        }
    }
}
