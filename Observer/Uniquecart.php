<?php

namespace Punchout2go\Punchout\Observer;

use Magento\Checkout\Model\Session as MageCheckoutSession;
use Magento\Customer\Model\Session as MageCustomerSession;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Persistent\Helper\Data as MagePersistentData;
use Magento\Persistent\Helper\Session as MagePersistentSession;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\QuoteFactory as MageQuoteFactory;
use Punchout2go\Punchout\Model\Session as PUNSession;

class Uniquecart implements ObserverInterface
{
    /**
     * Customer session
     *
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * Checkout session
     *
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * Persistent session
     *
     * @var \Magento\Persistent\Helper\Session
     */
    protected $persistentSession = null;

    /**
     * Persistent data
     *
     * @var \Magento\Persistent\Helper\Data
     */
    protected $persistentData = null;

    /**
     * @var null|\Punchout2go\Punchout\Model\Session
     */
    protected $punchoutSession = null;

    /** @var \Magento\Quote\Model\QuoteFactory|null */
    protected $quoteFactory = null;

    /** @var \Magento\Quote\Api\CartRepositoryInterface|null */
    protected $quoteRepository = null;

    protected $cart = null;

    /**
     * @param \Magento\Persistent\Helper\Session         $persistentSession
     * @param \Magento\Persistent\Helper\Data            $persistentData
     * @param \Magento\Customer\Model\Session            $customerSession
     * @param \Magento\Checkout\Model\Session            $checkoutSession
     * @param \Punchout2go\Punchout\Model\Session        $punchoutSession
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     * @param \Magento\Quote\Model\QuoteFactory          $quoteFactory
     */
    public function __construct(
        MagePersistentSession $persistentSession,
        MagePersistentData $persistentData,
        MageCustomerSession $customerSession,
        MageCheckoutSession $checkoutSession,
        PUNSession $punchoutSession,
        CartRepositoryInterface $quoteRepository,
        \Magento\Checkout\Model\Cart $userCart,
        MageQuoteFactory $quoteFactory
    ) {
        $this->cart = $userCart;
        $this->punchoutSession = $punchoutSession;
        $this->persistentSession = $persistentSession;
        $this->customerSession = $customerSession;
        $this->checkoutSession = $checkoutSession;
        $this->persistentData = $persistentData;
        $this->quoteFactory = $quoteFactory;
        $this->quoteRepository = $quoteRepository;
    }

    /**
     * Set quote to be loaded even if not active
     *
     * @param \Magento\Framework\Event\Observer $observer
     *
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(EventObserver $observer)
    {
        if ($this->punchoutSession->isPunchoutSession()) {
            $eventName = $observer->getEvent()->getName();
            if ($eventName === 'custom_quote_process' && $this->punchoutSession->isInSetup()) {
                $this->punchoutSession->helper->debug('this is a session init.');

                $loginType = $this->punchoutSession->helper->getConfig('punchout2go_punchout/session/type');

                if ($loginType != 'login'
                    || $this->customerSession->isLoggedIn() == true) {
                    $this->punchoutSession->helper->debug('prepare new cart.');
                    $this->punchoutSession->setInSetup(false);
                    $this->clearCurrentCart();
                    $quote = $this->createNewQuote();
                    $this->punchoutSession->helper->debug("Observer/Uniquecart::execute: After this->createNewQuote()");
                    // set new
                    $this->checkoutSession->setQuoteId($quote->getId());
                    $this->punchoutSession->helper
                        ->debug("Observer/Uniquecart::execute: After this->checkoutSession->setQuoteId(quote->getId()");
                    $this->checkoutSession->setLoadInactive(false);
                    $this->punchoutSession->helper
                        ->debug("Observer/Uniquecart::execute: After this->checkoutSession->setLoadInactive(false)");
                    $this->punchoutSession->setPunchoutCart($quote->getId());
                    $this->punchoutSession->helper
                        ->debug('new quote id :' . $this->checkoutSession->getQuoteId());

                    $this->punchoutSession->helper
                        ->debug('disable setup flag, only override once.');

                } else {
                    $this->punchoutSession->helper->debug('Login requires, but no user logged in. too early..?');
                }

            } elseif ($eventName !== 'custom_quote_process' && !$this->punchoutSession->isInSetup()) {
                $punchoutCart = $this->punchoutSession->getPunchoutCart();
                $quoteId = $this->checkoutSession->getQuoteId();
                if ($quoteId !== $punchoutCart) {
                    $this->punchoutSession->helper
                        ->debug('non-matching. prepare new cart from : ' . $quoteId);
                    $this->punchoutSession->helper
                        ->debug('New cart not from' . $this->punchoutSession->getPunchoutCart());
                    $this->clearCurrentCart();
                    $quote = $this->createNewQuote();
                    // set new
                    $this->checkoutSession->setQuoteId($quote->getId());
                    $this->punchoutSession->setPunchoutCart($quote->getId());
                    $this->punchoutSession->helper->debug('new quote id :' . $this->checkoutSession->getQuoteId());
                    $this->checkoutSession->setLoadInactive(false);
                    $this->punchoutSession->setInSetup(false);
                    $this->punchoutSession->helper->debug('disable setup flag, only override once.');
                }
            }
        }
    }

    /**
     * @return \Magento\Quote\Model\Quote
     */
    public function createNewQuote()
    {
        /** @var $quote \Magento\Quote\Model\Quote */
        $quote = $this->quoteFactory->create();
        $quote->setStore($this->punchoutSession->storeManager->getStore());
        if ($this->customerSession->isLoggedIn()) {
            $this->punchoutSession->helper
                ->debug('setting customer : '. $this->customerSession->getCustomer()->getId());
            $quote->setCustomerId($this->customerSession->getCustomer()->getId());
            $this->punchoutSession->helper
                ->debug("After quote->setCustomerId(this->customerSession->getCustomer()->getId())");
            $quote->setCustomer($this->customerSession->getCustomerDataObject());
            $this->punchoutSession->helper
                ->debug("getCustomerDataObject->getId() " . $this->customerSession->getCustomerDataObject()->getId());
            $this->punchoutSession->helper
                ->debug("After quote->setCustomer(this->customerSession->getCustomerDataObject())");
        } else {
            $this->punchoutSession->helper->debug('no customer attached to cart');
        }
        $quoteStoreId = $quote->getStore()->getId();
        $this->punchoutSession->helper->debug('Attempting to save quote object with store ID ' . $quoteStoreId);
        $this->quoteRepository->save($quote);
        $this->punchoutSession->helper
            ->debug("Observer/Uniquecart::createNewQuote: After this->quoteRepository->save(quote)");
        return $quote;
    }

    public function clearCurrentCart()
    {
        //$this->checkoutSession->clearQuote(); - calls other observers.
        $this->checkoutSession->setQuoteId(null);
        $this->checkoutSession->setLastSuccessQuoteId(null);
        //$this->checkoutSession->clearHelperData(); // okay, clear this..
        $this->checkoutSession->clearStorage(); // null's actual quote.
    }
}
