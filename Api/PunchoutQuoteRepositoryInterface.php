<?php
declare(strict_types=1);

namespace Punchout2Go\Punchout\Api;

use Punchout2Go\Punchout\Api\Data\PunchoutQuoteInterface;

/**
 * Class PunchoutQuoteRepositoryInterface
 * @package Punchout2Go\Punchout\Api
 */
interface PunchoutQuoteRepositoryInterface
{
    /**
     * @param int $entityId
     * @return PunchoutQuoteInterface
     */
    public function get(int $entityId): PunchoutQuoteInterface;

    /**
     * @param int $quoteId
     * @return PunchoutQuoteInterface
     */
    public function getByQuoteId(int $quoteId): PunchoutQuoteInterface;

    /**
     * @param string $punchoutId
     * @return PunchoutQuoteInterface
     */
    public function getByPunchoutId(string $punchoutId): PunchoutQuoteInterface;

    /**
     * @param PunchoutQuoteInterface $entity
     * @return bool
     */
    public function delete(PunchoutQuoteInterface $entity): bool;

    /**
     * @param int $entityId
     * @return bool
     */
    public function deleteById(int $entityId): bool;

    /**
     * @param PunchoutQuoteInterface $punchoutQuote
     */
    public function save(PunchoutQuoteInterface $punchoutQuote): PunchoutQuoteInterface;
}
