<?php
declare(strict_types=1);

namespace Punchout2Go\Punchout\Model;

use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\CartRepositoryInterface;
use Punchout2Go\Punchout\Api\PunchoutQuoteTransferInterface;
use Punchout2Go\Punchout\Api\TransferCartDataInterface;

/**
 * Class PunchoutQuoteTransfer
 * @package Punchout2Go\Punchout\Model
 */
class PunchoutQuoteTransfer implements PunchoutQuoteTransferInterface
{
    /**
     * @var CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * @var \Punchout2Go\Punchout\Api\PunchoutQuoteRepositoryInterface
     */
    protected $punchoutQuoteRepository;

    /**
     * @var Transfer\QuoteTransferDataFactory
     */
    protected $transferFactory;

    /**
     * PunchoutQuoteTransfer constructor.
     * @param CartRepositoryInterface $quoteRepository
     * @param \Punchout2Go\Punchout\Api\PunchoutQuoteRepositoryInterface $punchoutQuoteRepository
     * @param Transfer\QuoteTransferDataFactory $transferFactory
     */
    public function __construct(
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Punchout2Go\Punchout\Api\PunchoutQuoteRepositoryInterface $punchoutQuoteRepository,
        \Punchout2Go\Punchout\Model\Transfer\QuoteTransferDataFactory $transferFactory
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->transferFactory = $transferFactory;
        $this->punchoutQuoteRepository = $punchoutQuoteRepository;
    }

    /**
     * @param string $punchoutQuoteId
     * @return TransferCartDataInterface
     * @throws LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getTransferData(string $punchoutQuoteId): TransferCartDataInterface
    {
        try {
            $punchoutQuote = $this->punchoutQuoteRepository->getByPunchoutId($punchoutQuoteId);
            $quote = $this->quoteRepository->get($punchoutQuote->getQuoteId());

            return $this->transferFactory->create([
                'cart' => $quote->collectTotals(),
                'data' =>  [
                    'cart' => [
                        'punchout_session_id' => $punchoutQuote->getPunchoutSessionId(),
                        'punchout_return_url' => $punchoutQuote->getReturnUrl()
                    ]
                ]
            ]);
        } catch (LocalizedException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new LocalizedException(__('Something went wrong, please, try again later'));
        }
    }
}
