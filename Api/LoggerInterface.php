<?php
declare(strict_types=1);

namespace Punchout2Go\Punchout\Api;

/**
 * Interface LoggerInterface
 * @package Punchout2Go\Punchout\Api
 */
interface LoggerInterface
{
    public function log(string $string, array $context = []): bool;
}
