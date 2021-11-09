<?php
declare(strict_types=1);

namespace Punchout2Go\Punchout\Api;

/**
 * Interface RequestParamsHandler
 * @package Punchout2Go\Punchout\Api
 */
interface RequestParamsHandlerInterface
{
    /**
     * @param array $params
     * @return mixed
     */
    public function handle(array $params = []): array;
}
