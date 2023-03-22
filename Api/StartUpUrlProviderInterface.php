<?php
declare(strict_types=1);

namespace Punchout2Go\Punchout\Api;

use Punchout2Go\Punchout\Api\SessionInterface;

/**
 * Interface StartUpUrlProviderInterface
 * @package Punchout2Go\Punchout\Api
 */
interface StartUpUrlProviderInterface
{
    /**
     * @param \Punchout2Go\Punchout\Api\SessionInterface $session
     * @return string
     */
    public function getUrl(SessionInterface $session): string;
}
