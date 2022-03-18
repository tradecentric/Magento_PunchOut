<?php
namespace Punchout2go\Punchout\Plugin\Checkout;

use Punchout2go\Punchout\Helper\Data as HelperData;
use Punchout2go\Punchout\Model\Session as PUNSession;

class TotalPlugin
{
    /**
     * hold the cart id for the request.
     *
     * @var int
     */
    protected static $_cartId = 0;

    /**
     * @var \Punchout2go\Punchout\Helper\Data
     */
    protected $helper;

    /**
     * @var \Punchout2go\Punchout\Model\Session
     */
    protected $punchoutSession;
    /**
     * Cart total repository.
     *
     * @var \Magento\Quote\Api\CartTotalRepositoryInterface
     */
    protected $cartTotalRepository;

    /**
     * Quote repository.
     *
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $cartRepository;

    /**
     * plugin constructor.
     *
     * @param \Magento\Quote\Api\CartRepositoryInterface $cartRepository
     * @param \Magento\Quote\Api\CartTotalRepositoryInterface $cartTotalRepository
     * @param \Punchout2go\Punchout\Helper\Data   $dataHelper
     * @param \Punchout2go\Punchout\Model\Session $punchoutSession
     */
    public function __construct(
        \Magento\Quote\Api\CartRepositoryInterface $cartRepository,
        \Magento\Quote\Api\CartTotalRepositoryInterface $cartTotalRepository,
        HelperData $dataHelper,
        PUNSession $punchoutSession
    ) {
        $this->cartRepository = $cartRepository;
        $this->cartTotalRepository = $cartTotalRepository;
        $this->helper = $dataHelper;
        $this->punchoutSession = $punchoutSession;
    }

    /**
     * @param \Magento\Checkout\Model\TotalsInformationManagement\Interceptor $subject
     * @param int $cartId
     * @param \Magento\Checkout\Api\Data\TotalsInformationInterface $addressInformation
     * @return mixed
     */
    public function beforeCalculate(
        $subject,
        $cartId,
        $addressInformation
    ) {
        if ($this->punchoutSession->isPunchoutSession()) {
            self::$_cartId = $cartId;
        }
        return null;
    }

    /**
     * @param \Magento\Checkout\Model\TotalsInformationManagement\Interceptor $subject
     * @param \Magento\Quote\Model\Cart\Totals $result
     * @return mixed
     */
    public function afterCalculate($subject, $result)
    {
        $isPunchoutActive = $this->helper->getConfigFlag('punchout2go_punchout/security/punchout_active');
        if (true == $isPunchoutActive) {
            if ($this->punchoutSession->isPunchoutSession()) {
                $cartId = self::$_cartId;
                $quote = $this->cartRepository->get($cartId);
                $shipping = $quote->getShippingAddress();

                $this->helper->debug('save quote : ' . $quote->getId());
                $this->helper->debug('with : ' . $shipping->getShippingMethod());

                $shipping->save();
                $quote->save();
            }
        }
        return $result;
    }
}
