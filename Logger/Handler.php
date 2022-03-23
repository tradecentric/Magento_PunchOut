<?php
declare(strict_types=1);

namespace Punchout2Go\Punchout\Logger;

use Punchout2Go\Punchout\Api\LoggerInterface;

/**
 * Class Handler
 * @package Punchout2Go\Punchout\Logger
 */
class Handler implements LoggerInterface
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \Punchout2Go\Punchout\Helper\Data
     */
    protected $helper;

    /**
     * Handler constructor.
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Punchout2Go\Punchout\Helper\Data $helper
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Punchout2Go\Punchout\Helper\Data $helper
    ) {
        $this->logger = $logger;
        $this->helper = $helper;
    }

    /**
     * @param string $string
     * @param array $context
     * @return false|void|null
     */
    public function log(string $string, array $context = []) : bool
    {
        if ($this->helper->isLog()) {
            return (bool) $this->logger->debug($string, $context);
        }
        return false;
    }
}
