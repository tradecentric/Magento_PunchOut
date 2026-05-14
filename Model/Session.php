<?php
declare(strict_types=1);

namespace Punchout2Go\Punchout\Model;

use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Framework\App\State;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\LocalizedException as SessionException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Session\Config\ConfigInterface;
use Magento\Framework\Session\SaveHandlerInterface;
use Magento\Framework\Session\SessionManager;
use Magento\Framework\Session\SessionStartChecker;
use Magento\Framework\Session\SidResolverInterface;
use Magento\Framework\Session\StorageInterface;
use Magento\Framework\Session\ValidatorInterface;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\Quote\Address as CustomerAddressConverter;
use Punchout2Go\Punchout\Api\Data\PunchoutQuoteInterface;
use Punchout2Go\Punchout\Api\Data\PunchoutQuoteInterfaceFactory;
use Punchout2Go\Punchout\Api\PunchoutQuoteRepositoryInterface;
use Punchout2Go\Punchout\Api\SessionContainerInterface;
use Punchout2Go\Punchout\Api\SessionContainerInterfaceFactory;
use Punchout2Go\Punchout\Api\SessionInterface;
use Punchout2Go\Punchout\Model\System\Config\Source\Login;
use Magento\Quote\Api\CartManagementInterface;

/**
 * Class Session
 * @package Punchout2Go\Punchout\Model
 */
class Session extends SessionManager implements SessionInterface
{
    /**
     * @var \Punchout2Go\Punchout\Api\LoggerInterface
     */
    protected $logger;

    /** @var PunchoutPreLoginCollector */
    protected $preLoginCollector;

    /** @var PunchoutPostLoginCollector */
    protected $postLoginCollector;
    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager;

    /**
     * @var \Punchout2Go\Punchout\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var SessionContainerInterfaceFactory
     */
    protected $containerFactory;

    /**
     * @var CartRepositoryInterface
     */
    protected $cartRepository;

    /** @var CartManagementInterface */
    protected $cartManagement;

    /** @var AddressRepositoryInterface */
    protected $addressRepository;

    /** @var CustomerAddressConverter */
    protected $customerAddressConverter;

    /** @var CustomerInterfaceFactory */
    protected $customerDataFactory;

    /**
     * @var PunchoutQuoteRepositoryInterface
     */
    protected $punchoutQuoteRepository;

    /**
     * @var PunchoutQuoteInterfaceFactory
     */
    protected $punchoutQuoteInterfaceFactory;

    /**
     * @var PunchoutQuoteInterface|null
     */
    protected $punchoutSession = null;

    /**
     * @var Session\SessionEditStatus
     */
    protected $editStatus;

    /**
     * Session constructor.
     * @param \Magento\Framework\App\Request\Http $request
     * @param SidResolverInterface $sidResolver
     * @param ConfigInterface $sessionConfig
     * @param SaveHandlerInterface $saveHandler
     * @param ValidatorInterface $validator
     * @param StorageInterface $storage
     * @param CookieManagerInterface $cookieManager
     * @param CookieMetadataFactory $cookieMetadataFactory
     * @param State $appState
     * @param \Punchout2Go\Punchout\Api\LoggerInterface $logger
     * @param \Punchout2Go\Punchout\Api\EntityHandlerInterface $preLoginCollector
     * @param \Punchout2Go\Punchout\Api\EntityHandlerInterface $postLoginCollector
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Punchout2Go\Punchout\Helper\Data $helper
     * @param SessionContainerInterfaceFactory $containerFactory
     * @param CartRepositoryInterface $cartRepository
     * @param CartManagementInterface $cartManagement
     * @param Session\SessionEditStatus $editStatus
     * @param AddressRepositoryInterface $addressRepository
     * @param CustomerAddressConverter $customerAddressConverter
     * @param PunchoutQuoteRepositoryInterface $punchoutQuoteRepository
     * @param PunchoutQuoteInterfaceFactory $punchoutQuoteInterfaceFactory
     * @param SessionStartChecker|null $sessionStartChecker
     * @throws \Magento\Framework\Exception\SessionException
     */
    public function __construct(
        \Magento\Framework\App\Request\Http $request,
        SidResolverInterface $sidResolver,
        ConfigInterface $sessionConfig,
        SaveHandlerInterface $saveHandler,
        ValidatorInterface $validator,
        StorageInterface $storage,
        CookieManagerInterface $cookieManager,
        CookieMetadataFactory $cookieMetadataFactory,
        State $appState,
        \Punchout2Go\Punchout\Api\LoggerInterface $logger,
        \Punchout2Go\Punchout\Api\EntityHandlerInterface $preLoginCollector,
        \Punchout2Go\Punchout\Api\EntityHandlerInterface $postLoginCollector,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Punchout2Go\Punchout\Helper\Data $helper,
        SessionContainerInterfaceFactory $containerFactory,
        CartRepositoryInterface $cartRepository,
        CartManagementInterface $cartManagement,
        Session\SessionEditStatus $editStatus,
        AddressRepositoryInterface $addressRepository,
        CustomerAddressConverter $customerAddressConverter,
        CustomerInterfaceFactory $customerDataFactory,
        PunchoutQuoteRepositoryInterface $punchoutQuoteRepository,
        PunchoutQuoteInterfaceFactory $punchoutQuoteInterfaceFactory,
        ?SessionStartChecker $sessionStartChecker = null
    ) {
        $this->logger = $logger;
        $this->preLoginCollector = $preLoginCollector;
        $this->postLoginCollector = $postLoginCollector;
        $this->eventManager = $eventManager;
        $this->helper = $helper;
        $this->customerSession = $customerSession;
        $this->checkoutSession = $checkoutSession;
        $this->containerFactory = $containerFactory;
        $this->cartRepository = $cartRepository;
        $this->cartManagement = $cartManagement;
        $this->editStatus = $editStatus;
        $this->addressRepository = $addressRepository;
        $this->customerAddressConverter = $customerAddressConverter;
        $this->customerDataFactory = $customerDataFactory;
        $this->punchoutQuoteRepository = $punchoutQuoteRepository;
        $this->punchoutQuoteInterfaceFactory = $punchoutQuoteInterfaceFactory;
        parent::__construct(
            $request,
            $sidResolver,
            $sessionConfig,
            $saveHandler,
            $validator,
            $storage,
            $cookieManager,
            $cookieMetadataFactory,
            $appState,
            $sessionStartChecker
        );
    }

    /**
     * @param array $params
     * @throws SessionException
     */
    public function startSession(array $params): void
    {
        if (!$this->helper->isPunchoutActive()) {
            throw new LocalizedException(__('PunchOut is not active at this scope.'));
        }

        $this->storage->setData($params);
        $this->logger->log('Running PunchOut Setup', $params);

        if (!$this->isValid()) {
            throw new SessionException(
                __('PunchOut session request is invalid.')
            );
        }

        // Build container with a fresh customer DataModel.
        $container = $this->containerFactory->create([
           'session' => $this->getPunchoutQuote(),
           'quote' => $this->checkoutSession->getQuote(),
           'customer' => $this->customerDataFactory->create(),
        ]);

        // Fire starting session event and logout customer
        $this->sessionPreStart($container);

        // Resolve the PunchOut customer; login only in LOGIN_LOGGED_IN mode.
        // Anonymous merchants ship a first-class config option (Login::LOGIN_ANONYMOUS)
        // and must not be silently re-logged-in as the prior storefront visitor.
        $this->preLoginCollector->handle($container);
        if ($this->helper->getCustomerSessionType() === Login::LOGIN_LOGGED_IN) {
            $this->loginCustomer($container->getCustomer());
            $this->logger->log('Resolve customer and login');
        } else {
            $this->logger->log('Anonymous session: skipping customer login');
        }

        // Resolve THIS sid's quote from punchout_quote.quote_id, not from checkoutSession.
        $quote = $this->initQuote();
        $container->setQuote($quote);

        if ($this->getOperation() !== 'inspect') {
            $this->postLoginCollector->handle($container);

            // Default-address copy only applies when there's a logged-in customer
            if (!$this->isAnonymousSession() && $this->helper->isMageAddressToCart()) {
                $this->applyCustomerAddresses($quote);
            }
        } else {
            $this->logger->log('Inspect session: read-only, skipping item/address reconciliation');
        }

        // Save and link quote
        $quote->setTotalsCollectedFlag(false)->collectTotals();
        $this->cartRepository->save($quote);
        $this->checkoutSession->setQuoteId((int) $quote->getId());

        // save punchout quote — only bind on first save; never overwrite a bound sid
        $punchoutQuote = $container->getSession();
        $existingBound = (int) $punchoutQuote->getQuoteId();
        if (!$existingBound) {
            $punchoutQuote->setQuoteId((int) $quote->getId());
        } elseif ($existingBound !== (int) $quote->getId()) {
            throw new SessionException(__(
                'Punchout session %1 is bound to quote %2 but tried to switch to quote %3',
                $punchoutQuote->getPunchoutSessionId(),
                $existingBound,
                $quote->getId()
            ));
        }
        $this->punchoutQuoteRepository->save($punchoutQuote);
        $this->logger->log('Collect Totals, cart save Complete');

        $this->sessionPostStart($container);
        $this->logger->log('Session start completed');
    }

    /**
     * @return mixed
     * @throws SessionException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function getContainer()
    {
        return $this->containerFactory->create(
            [
                'session' => $this->getPunchoutQuote(),
                'quote' => $this->initQuote(),
                'customer' => $this->customerSession->getCustomer()->getDataModel()
            ]
        );
    }

    /**
     * @throws NoSuchEntityException
     * @throws SessionException
     */
    private function initQuote(): CartInterface
    {
        $punchoutQuote = $this->getPunchoutQuote();
        $boundQuoteId  = (int) $punchoutQuote->getQuoteId();
        $operation     = $this->getOperation();

        // Anonymous sessions follow the same model but without customer scoping.
        if ($this->isAnonymousSession()) {
            return $this->initAnonymousQuote($boundQuoteId, $operation);
        }

        $customerId = (int) $this->customerSession->getCustomerId();

        if ($operation === 'create') {
            if ($boundQuoteId) {
                $this->logger->log(sprintf('create for already-bound sid; reusing quote %d', $boundQuoteId));
                return $this->loadAndActivate($boundQuoteId, $customerId);
            }
            return $this->mintFreshQuoteForCustomer($customerId);
        }

        // edit / inspect: every setuprequest carries its own pos/sid, so the
        // bound-quote lookup above only hits on a retry of the same edit. On
        // the first edit, derive the target quote_id from the inbound items'
        // secondaryId ("{quote_id}/{item_id}"). The back-write gate in
        // startSession() then binds this new sid → that quote_id for retries.
        if (!$boundQuoteId) {
            $boundQuoteId = $this->extractTargetQuoteIdFromPayload();
            if (!$boundQuoteId) {
                throw new SessionException(__(
                    'Edit/inspect arrived with no resolvable target quote (no secondaryId on inbound items).'
                ));
            }
        }
        $quote = $this->loadAndActivate($boundQuoteId, $customerId);
        if ((int) $quote->getCustomerId() !== $customerId) {
            throw new SessionException(__('Edit/inspect target quote does not belong to this customer.'));
        }
        return $quote;
    }

    private function isAnonymousSession(): bool
    {
        return $this->helper->getCustomerSessionType() === Login::LOGIN_ANONYMOUS;
    }

    /**
     * @throws NoSuchEntityException
     * @throws SessionException
     */
    private function initAnonymousQuote(int $boundQuoteId, string $operation): CartInterface
    {
        if ($operation === 'create') {
            if ($boundQuoteId) {
                $this->logger->log(sprintf('anonymous create for already-bound sid; reusing guest quote %d', $boundQuoteId));
                return $this->loadGuestQuote($boundQuoteId);
            }
            return $this->mintFreshGuestQuote();
        }

        if (!$boundQuoteId) {
            $boundQuoteId = $this->extractTargetQuoteIdFromPayload();
            if (!$boundQuoteId) {
                throw new SessionException(__(
                    'Anonymous edit/inspect arrived with no resolvable target quote (no secondaryId on inbound items).'
                ));
            }
        }
        $quote = $this->loadGuestQuote($boundQuoteId);
        // Inverse tenancy check: an anonymous sid must not be allowed to claim
        // a login-mode customer's quote via a crafted secondaryId.
        if ((int) $quote->getCustomerId() !== 0) {
            throw new SessionException(__('Anonymous session cannot target a customer-bound quote.'));
        }
        return $quote;
    }

    /**
     * @throws CouldNotSaveException
     * @throws NoSuchEntityException
     */
    private function mintFreshGuestQuote(): CartInterface
    {
        $newQuoteId = (int) $this->cartManagement->createEmptyCart();
        return $this->cartRepository->get($newQuoteId);
    }

    /**
     * @throws NoSuchEntityException
     */
    private function loadGuestQuote(int $quoteId): CartInterface
    {
        $quote = $this->cartRepository->get($quoteId); // works on inactive quotes
        if (!$quote->getIsActive()) {
            $quote->setIsActive(true);
        }
        return $quote;
    }

    /**
     * Inspect the inbound payload's items and return the first quote_id
     * referenced via secondaryId ("{quote_id}/{item_id}"). Returns 0 if no
     * item carries a parseable secondaryId.
     */
    private function extractTargetQuoteIdFromPayload(): int
    {
        $items = (array) $this->storage->getData('params/body/items');
        foreach ($items as $item) {
            $secondaryId = (string) ($item['secondaryId'] ?? '');
            if ($secondaryId === '') {
                continue;
            }
            $parts = $this->helper->getQuoteItemIdInfo($secondaryId);
            $quoteId = isset($parts[0]) ? (int) $parts[0] : 0;
            if ($quoteId > 0) {
                return $quoteId;
            }
        }
        return 0;
    }

    /**
     * @throws NoSuchEntityException
     * @throws CouldNotSaveException
     * @throws SessionException
     */
    private function mintFreshQuoteForCustomer(int $customerId): CartInterface
    {
        if (!$customerId) {
            throw new SessionException(__('Cannot mint a quote without a logged-in customer.'));
        }

        // Magento allows only one active quote per customer.
        // edit/inspect for the prior sid will reactivate it via loadAndActivate().
        $this->deactivateOtherActiveQuotes($customerId, 0);
        $newQuoteId = (int) $this->cartManagement->createEmptyCartForCustomer($customerId);
        return $this->cartRepository->get($newQuoteId);
    }

    private function loadAndActivate(int $quoteId, int $customerId): CartInterface
    {
        // Make this sid's quote the customer's sole active cart. Park whichever
        // quote is currently active (if it isn't already this one).
        $this->deactivateOtherActiveQuotes($customerId, $quoteId);
        $quote = $this->cartRepository->get($quoteId); // works on inactive quotes
        if (!$quote->getIsActive()) {
            $quote->setIsActive(true);
        }
        return $quote;
    }

    private function deactivateOtherActiveQuotes(int $customerId, int $keepQuoteId): void
    {
        if (!$customerId) {
            return;
        }
        try {
            $active = $this->cartRepository->getActiveForCustomer($customerId);
        } catch (NoSuchEntityException $e) {
            return; // no active quote — nothing to park
        }
        if ((int) $active->getId() === $keepQuoteId) {
            return;
        }
        $active->setIsActive(false);
        $this->cartRepository->save($active);
    }


    /**
     * @return PunchoutQuoteInterface
     */
    public function getPunchoutQuote(): PunchoutQuoteInterface
    {
        if ($this->punchoutSession === null) {
            try {
                $this->punchoutSession = $this->punchoutQuoteRepository->getByPunchoutId($this->getPunchoutSessionId());
                $this->punchoutSession->setParams($this->storage->getData('params'));
            } catch (NoSuchEntityException $e) {
                $this->punchoutSession = $this->getNewPunchoutSession();
            } catch (\Exception $e) {
                $this->logger->log($e->getMessage());
                throw new LocalizedException(__('Punchout Session Error'));
            }
        }
        return $this->punchoutSession;
    }

    /**
     * @return PunchoutQuoteInterface
     */
    protected function getNewPunchoutSession() : PunchoutQuoteInterface
    {
        /** @var PunchoutQuoteInterface $empty */
        $empty = $this->punchoutQuoteInterfaceFactory->create();
        $empty->setParams($this->getParams())
            ->setPunchoutSessionId($this->getPunchoutSessionId())
            ->setReturnUrl($this->getReturnUrl());
        return $empty;
    }

    /**
     * @param SessionContainerInterface $container
     * @throws SessionException
     */
    protected function sessionPostStart(SessionContainerInterface $container): void
    {
        $metadata = $this->cookieMetadataFactory
            ->createPublicCookieMetadata()
            ->setDuration(86400)
            //->setSameSite('None')
            ->setPath($this->getCookiePath() ?? '/')
            ->setDomain($this->getCookieDomain() ?? '/')
            ->setSecure(true);

        $this->cookieManager->setPublicCookie(
            'punchout_session_key',
            $container->getSession()->getPunchoutSessionId(),
            $metadata
        );

        $this->eventManager->dispatch('punchout_session_ready', ['session' => $container]);
    }

    /**
     * pre start actions
     */
    protected function sessionPreStart(SessionContainerInterface $container): void
    {
        $this->eventManager->dispatch('punchout_session_starting', ['session' => $container]);
        $this->checkLock();
        $this->logoutCustomer();
        $this->prepareQuote();
    }

    /**
     *
     * login customer
     */
    protected function loginCustomer(?\Magento\Customer\Api\Data\CustomerInterface $customer = null)
    {
        if ($customer && $customer->getId()) {
            $this->customerSession->loginById($customer->getId());
            $this->logger->log("Customer selected");
        } else {
            throw new SessionException(
                __('Unable to login without a default user.')
            );
        }
    }

    /**
     * logout customer and prepare to start session
     */
    protected function logoutCustomer()
    {
        if ($this->customerSession->isSessionExists()) {
            $this->logger->log('Clear customer session');
            $this->customerSession->setCartWasUpdated(true);
            $this->customerSession->unsetLastAddedProductId();
            // $this->clearCustomerSession();
        }

        if ($this->customerSession->isLoggedIn()) {
            $this->logger->log('Log out current customer');
            $this->customerSession->logout();
        }
    }

    /**
     * Update quote address from a given customer address
     *
     * @param CartInterface $quote
     * @param int $customerId
     * @param int $addressId
     * @param string $type shipping|billing
     * @return void
     * @throws LocalizedException
     */
    public function updateQuoteAddressFromCustomerAddress(CartInterface $quote, $customerAddress, $type = 'shipping')
    {
        if ($customerAddress) {
            $quoteAddress = ($type === 'billing')
                ? $quote->getBillingAddress()
                : $quote->getShippingAddress();

            $this->customerAddressConverter->importCustomerAddressData($customerAddress);
            $quoteAddress->importCustomerAddressData($customerAddress);

            if ($type === 'shipping') {
                $quoteAddress->setCollectShippingRates(true);
            }

            $quote->collectTotals()->save();
        }
    }

    /**
     * clear quote
     */
    protected function prepareQuote(): void
    {
        $quoteId = $this->checkoutSession->getQuoteId();
        if ($quoteId !== (int)$this->getSessionId()) {
            $this->checkoutSession->clearQuote();
        }
    }

    /**
     * check session status
     */
    protected function checkLock()
    {
        $isEditable = (bool) $this->editStatus->getEditStatus($this->getParams());
        $this->storage->setData('is_editable', $isEditable);
    }

    /**
     * @return string
     */
    public function getPunchoutSessionId(): string
    {
        return (string) $this->storage->getData(static::PUNCHOUT_SESSION);
    }

    /**
     * @return string
     */
    public function getSessionId(): string
    {
        return (string) $this->storage->getData(static::SESSION_ID);
    }

    /**
     * @return string
     */
    public function getReturnUrl(): string
    {
        return (string) $this->storage->getData(static::RETURN_URL);
    }

    /**
     * @return mixed[]
     */
    public function getParams(): array
    {
        return (array) $this->storage->getData(static::PARAMS);
    }

    /**
     * @return string
     */
    public function getOperation(): string
    {
        return (string) ($this->getParams()['operation'] ?? '');
    }

    /**
     * @return bool
     */
    public function isEdit(): bool
    {
        return (bool) $this->storage->getData('is_editable');
    }

    /**
     * @return bool
     */
    public function isValid(): bool
    {
        return (bool) $this->getPunchoutSessionId() && (bool) $this->getReturnUrl();
    }

    /**
     * lock session
     */
    public function lockSession(): void
    {
        $this->storage->setData('is_editable', Session\SessionEditStatus::NOT_EDITABLE);
    }

    /**
     * destroy session
     */
    public function destroySession(): void
    {
        $this->logger->log("User has Session!");
        if ($this->customerSession->isLoggedIn()) {
            $this->logger->log("User is logged in!");
            $this->customerSession->logout();
        }
        $this->customerSession->destroy([]);
        $this->checkoutSession->destroy([]);
        $this->destroy(['send_expire_cookie'=>true]);
        $this->cookieManager->deleteCookie('punchout_session_key');
    }

    /**
     * @return string
     */
    public function getInItemSku(): string
    {
        $items = (array) $this->storage->getData('params/body/items');
        $items = array_filter($items, function (array $item) {
            return $item['type'] == "in";
        });
        $item = current($items);
        return $item['primaryId'] ?? '';
    }

    protected function applyCustomerAddresses(CartInterface $quote): void
    {
        $this->logger->log('Get Customer Addresses');
        $defaultShippingAddress = null;
        $defaultBillingAddress = null;

        $customer = $this->customerSession->getCustomer();

        // get DefaultShipping
        if ($customer->getDefaultShipping()) {
            $defaultShippingAddress = $this->addressRepository->getById($customer->getDefaultShipping());
        }

        if ($customer->getDefaultBilling()) {
            $defaultBillingAddress = $this->addressRepository->getById($customer->getDefaultBilling());
        }

        // get DefaultShipping
        if ($defaultShippingAddress) {
            $this->logger->log('Customer Default Shipping Address');
            $this->updateQuoteAddressFromCustomerAddress($quote, $defaultShippingAddress, 'shipping');
        }

        if ($defaultBillingAddress) {
            $this->logger->log('Customer Default Billing Address');
            $this->updateQuoteAddressFromCustomerAddress($quote, $defaultBillingAddress, 'billing');
        }
    }
}
