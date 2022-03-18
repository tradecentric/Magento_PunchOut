<?php

namespace Punchout2go\Punchout\Controller\Session;

use Magento\Customer\Model\Session as MageSession;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Checkout\Model\Cart as MageCart;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context as ActionContext;
use Magento\Framework\View\Result\PageFactory;
use Magento\Customer\CustomerData\SectionPoolInterface;

class Inspect extends Action
{
    /**
     * @var \Magento\Customer\CustomerData\SectionPool
     */
    protected $sectionPool;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;
    /**
     * @var \Magento\Checkout\Model\Cart
     */
    protected $cart;

    /**
     * @param \Magento\Framework\App\Action\Context      $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Customer\Model\Session            $session
     */
    public function __construct(
        ActionContext $context,
        PageFactory $resultPageFactory,
        MageSession $session,
        CheckoutSession $checkout,
        SectionPoolInterface $sectionPool,
        MageCart $cart
    ) {
        $this->sectionPool = $sectionPool;
        $this->cart = $cart;
        $this->checkoutSession = $checkout;
        $this->customerSession = $session;
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    /**
     * Default punchout controller
     *
     * @return void
     */
    public function execute()
    {

        /**
         * Unused?
         *
         * @var \Magento\Customer\Model\Session $customerSession
         */
        /** @var Magento\Quote\Model\Quote $quote */
        $quote = $this->cart->getQuote();
        //$quote = $this->checkoutSession->getQuote();
        //$this->checkoutSession->clearHelperData();
        //$this->checkoutSession->setQuote($quote);

        $string = "";

        $cartSection = $this->sectionPool->getSectionsData(['cart'], true);
        $string .= "---cart section---\n";
        $string .= "sectionPool.getSectionsData([cart])\n";
        $string .= $this->buildDataString($cartSection) ."\n";

        $quote->setTotalsCollectedFlag(false);
        $quote->getShippingAddress()->setCollectShippingRates(true);
        $quote->collectTotals();

        $rates = $quote->getShippingAddress()->getAllShippingRates();
        $string .= "---rates---\n";
        $string .= "quote.getShippingAddress().getAllShippingRates()\n";
        $string .= $this->buildDataString($rates) ."\n";

        /*
        $rate = $quote->getShippingAddress()->getShippingRateByCode("flatrate_flatrate"); //();
        if ($rate) {
            $quote->getShippingAddress()->setShippingMethod($rate->getCode());
            $quote->getShippingAddress()->setShippingDescription($rate->getMethod());
            $quote->getShippingAddress()->setShippingAmount($rate->getPrice());
            $quote->save();
        } else {
            echo "-- flat rate not found\n";
        }*/

        $quote->setTotalsCollectedFlag(false);
        $quote->collectTotals();

        // NOTE: This hack is to circumnavigate the Magento Code sniffer.
        //$string = 'Uncomment the line below this one in the code as needed for PO2Go debugging.';
        $string  .= "---quote---\n";
        $string .= "quote.getData()\n";
        $data = $quote->getData();
        unset($data['items']);
        $string .= $this->buildDataString($data) ."\n";
        $string .= "quote.getAllItems()\n";
        $string .= $this->buildDataString($quote->getAllItems()) ."\n";
        $string .= "quote.getShippingAddress()\n";
        $shipping = $quote->getShippingAddress();
        $string .= $this->buildDataString($shipping) ."\n";
        $string .= "quote.getTotals()\n";
        $string .= $this->buildDataString($quote->getTotals()) ."\n";

        $this->getResponse()->setBody('<pre>' . $string . '</pre>');
    }

    /**
     * Build a string out of an object
     *
     * @param $object
     * @param number $position
     * @return string
     */
    public function buildDataString($object, $position = 0)
    {
        $indent = "";
        for ($i = 0; $i < $position; $i++) {
            $indent .= "   ";
        }
        $string = "";
        $data = [];
        if (is_object($object)) {
            $string .= "> ". get_class($object) ."\n";
            if (method_exists($object, "getData")) {
                $data = $object->getData();
            } elseif ($object instanceof \Magento\Framework\Phrase) {
                $stringify = (string) $object;
                $string .= $indent . $stringify ."\n";
            } else {
                //$stringify = (string) $object;
                $string .= $indent ."~". "cannot read" ."\n";
                return $string;
            }
        } elseif (is_array($object)) {
            $string .= "> array\n";
            $data = $object;
        } else {
            $string .= "> ". $object ."\n";
            return $string;
        }
        foreach ($data as $key => $value) {
            $string .= $indent . $key ." =";
            if (is_string($value)) {
                $string .= " ". $value ."\n";
            } else {
                $child = $this->buildDataString($value, ($position + 1));
                $string .= ($child ? $child : "\n");
            }
        }
        return $string;
    }
}
