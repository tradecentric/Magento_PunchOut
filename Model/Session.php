<?php
declare(strict_types=1);

namespace Punchout2Go\Punchout\Model;

use Magento\Framework\App\State;
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
use Punchout2Go\Punchout\Api\Data\PunchoutQuoteInterface;
use Punchout2Go\Punchout\Api\Data\PunchoutQuoteInterfaceFactory;
use Punchout2Go\Punchout\Api\PunchoutQuoteRepositoryInterface;
use Punchout2Go\Punchout\Api\SessionContainerInterface;
use Punchout2Go\Punchout\Api\SessionContainerInterfaceFactory;
use Punchout2Go\Punchout\Api\SessionInterface;
use Punchout2Go\Punchout\Model\System\Config\Source\Login;

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

    /**
     * @var PunchoutSessionCollector
     */
    protected $sessionCollector;
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
     * @param \Punchout2Go\Punchout\Api\EntityHandlerInterface $sessionCollector
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Punchout2Go\Punchout\Helper\Data $helper
     * @param SessionContainerInterfaceFactory $containerFactory
     * @param CartRepositoryInterface $cartRepository
     * @param Session\SessionEditStatus $editStatus
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
        \Punchout2Go\Punchout\Api\EntityHandlerInterface $sessionCollector,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Punchout2Go\Punchout\Helper\Data $helper,
        SessionContainerInterfaceFactory $containerFactory,
        CartRepositoryInterface $cartRepository,
        Session\SessionEditStatus $editStatus,
        PunchoutQuoteRepositoryInterface $punchoutQuoteRepository,
        PunchoutQuoteInterfaceFactory $punchoutQuoteInterfaceFactory,
        SessionStartChecker $sessionStartChecker = null
    ) {
        $this->logger = $logger;
        $this->sessionCollector = $sessionCollector;
        $this->eventManager = $eventManager;
        $this->helper = $helper;
        $this->customerSession = $customerSession;
        $this->checkoutSession = $checkoutSession;
        $this->containerFactory = $containerFactory;
        $this->cartRepository = $cartRepository;
        $this->editStatus = $editStatus;
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
     * @param array $startupParams
     * @throws SessionException
     */
    public function startSession(array $startupParams): void
    {
        if (!$this->helper->isPunchoutActive()) {
            throw new LocalizedException(__('PunchOut is not active at this scope.'));
        }
        $this->logger->log('Running PunchOut Setup', $startupParams);
        $this->storage->setData($startupParams);
        if (!$this->isValid()) {
            throw new SessionException(
                __('PunchOut session request is invalid.')
            );
        }
        /** @var SessionContainerInterface $container */
        $container = $this->getContainer();
        $this->sessionPreStart($container);
        $this->sessionCollector->handle($container);
        $this->logger->log('Collect data complete');

        $this->sessionPostStart($container);
        $this->logger->log('Post start');

        /** save magento quote */
        $this->checkoutSession->clearStorage();
        $quote = $this->initQuote()->setTotalsCollectedFlag(false)->collectTotals();
        $container->setQuote($quote);
        $this->cartRepository->save($quote);

        /** save punchout quote */
        $punchoutQuote = $container->getSession()->setQuoteId((int)$quote->getId());
        $this->punchoutQuoteRepository->save($punchoutQuote);
        $this->logger->log('Collect Totals, cart save Complete');

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

    private function initQuote(): CartInterface {
        $isEdit = false;
        if (
            !empty($this->storage->getData()['params']['operation']) &&
            $this->storage->getData()['params']['operation'] === 'edit'
        ) $isEdit = true;

        $quote = $this->checkoutSession->getQuote();
        if (!$quote->isObjectNew() && !$isEdit) {
            $quote->setIsActive(false);
            $this->cartRepository->save($quote);
            $this->checkoutSession->clearStorage();
            return $this->initQuote();
        }

        $quote->setIsActive(true);
        return $quote;
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
    protected function sessionPostStart(SessionContainerInterface $container)
    {
        $this->eventManager->dispatch('punchout_session_ready', ['session' => $container]);
        if ($this->helper->getCustomerSessionType() == Login::LOGIN_LOGGED_IN) {
            $this->loginCustomer($container->getCustomer());
        }

        $metadata = $this->cookieMetadataFactory
            ->createPublicCookieMetadata()
            ->setDuration(86400)
            //->setSameSite('None')
            ->setPath($this->getCookiePath() ?? '/')
            ->setDomain($this->getCookieDomain() ?? '/')
            ->setSecure(true);
        $this->cookieManager->setPublicCookie('punchout_session_key', $container->getSession()->getPunchoutSessionId(), $metadata);
    }

    /**
     * pre start actions
     */
    protected function sessionPreStart(SessionContainerInterface $container)
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
    protected function loginCustomer(\Magento\Customer\Api\Data\CustomerInterface $customer = null)
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
     * clear quote
     */
    protected function prepareQuote()
    {
        $quoteId = $this->checkoutSession->getQuoteId();
        if ($quoteId != $this->getSessionId()) {
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
}
