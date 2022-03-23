<?php
declare(strict_types=1);

namespace Punchout2Go\Punchout\Api;

/**
 * Interface EntityHandlerInterface
 * @package Punchout2Go\Punchout\Api
 */
interface EntityHandlerInterface
{
    /**
     * @param SessionContainerInterface $container
     * @return mixed
     */
    public function handle(SessionContainerInterface $container);
}
