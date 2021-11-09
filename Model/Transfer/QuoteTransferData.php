<?php
declare(strict_types=1);

namespace Punchout2Go\Punchout\Model\Transfer;

use Magento\Framework\Exception\LocalizedException;
use Punchout2Go\Punchout\Api\CartDataInterface;
use Punchout2Go\Punchout\Api\CartDataInterfaceFactory;
use Punchout2Go\Punchout\Api\TransferCartDataInterface;

/**
 * Class QuoteTransferData
 * @package Punchout2Go\Punchout\Model\Transfer
 */
class QuoteTransferData extends \Magento\Framework\DataObject implements TransferCartDataInterface
{
    /**
     * @var QuoteDataPool
     */
    protected $dataPool;

    /**
     * @var \Magento\Quote\Api\Data\CartInterface
     */
    protected $cart;

    /**
     * @var CartDataInterfaceFactory
     */
    protected $cartDataInterfaceFactory;

    /**
     * @var QuoteDataHandlerInterface
     */
    protected $cartHandler;

    /**
     * @var QuoteDataHandlerInterface
     */
    protected $cartItemsHandler;

    /**
     * @var \Punchout2Go\Punchout\Api\LoggerInterface
     */
    protected $logger;

    /**
     * QuoteTransferData constructor.
     * @param \Magento\Quote\Api\Data\CartInterface $cart
     * @param CartDataInterfaceFactory $cartDataInterfaceFactory
     * @param QuoteDataHandlerInterface $cartHandler
     * @param QuoteDataHandlerInterface $cartItemsHandler
     * @param \Punchout2Go\Punchout\Api\LoggerInterface $logger
     * @param array $data
     * @throws LocalizedException
     */
    public function __construct(
        \Magento\Quote\Api\Data\CartInterface $cart,
        CartDataInterfaceFactory $cartDataInterfaceFactory,
        \Punchout2Go\Punchout\Model\Transfer\QuoteDataHandlerInterface $cartHandler,
        \Punchout2Go\Punchout\Model\Transfer\QuoteDataHandlerInterface $cartItemsHandler,
        \Punchout2Go\Punchout\Api\LoggerInterface $logger,
        array $data = []
    ) {
        $this->cart = $cart;
        $this->cartHandler = $cartHandler;
        $this->cartDataInterfaceFactory = $cartDataInterfaceFactory;
        $this->cartItemsHandler = $cartItemsHandler;
        $this->logger = $logger;
        parent::__construct($data);
        $this->assertCartValid();
        $this->assertSessionValid();
        $this->assertUrlValid();
        $this->prepareTransferData();
    }

    /**
     * @throws LocalizedException
     */
    protected function assertSessionValid()
    {
        if (!strlen($this->getData('cart/punchout_session_id'))) {
            throw new LocalizedException(__('Punchout session is not valid'));
        }
    }

    /**
     * @throws LocalizedException
     */
    protected function assertUrlValid()
    {
        if (!strlen($this->getData('cart/punchout_return_url'))) {
            throw new LocalizedException(__('Punchout url is not valid'));
        }
        if (!filter_var($this->getData('cart/punchout_return_url'), FILTER_VALIDATE_URL)) {
            throw new LocalizedException(__('Punchout url is not valid string'));
        }
    }

    /**
     * @throws LocalizedException
     */
    protected function assertCartValid()
    {
        if (!$this->cart->getId()) {
            throw new LocalizedException(__('Cart is not valid'));
        }
        if (!$this->cart->getItemsCount()) {
            throw new LocalizedException(__('Cart is empty'));
        }
    }

    /**
     * compose result data
     */
    protected function prepareTransferData()
    {
        $this->logger->log('Prepare transfer cart data');
        $this->setData('cart', $this->cartDataInterfaceFactory->create([
            'data' => array_merge($this->getData('cart'), $this->cartHandler->handle($this->cart))
        ]));
        $this->logger->log('Prepare transfer cart items data');
        $this->setData('items', $this->cartItemsHandler->handle($this->cart));
    }

    /**
     * @return CartDataInterface
     */
    public function getCartData(): CartDataInterface
    {
        return $this->getData('cart');
    }

    /**
     * @return array
     */
    public function getItemsData()
    {
        return $this->getData('items');
    }
}
