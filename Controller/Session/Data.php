<?php
namespace Punchout2go\Punchout\Controller\Session;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context as ActionContext;
use Magento\Framework\App\ResponseInterface;
use Punchout2go\Punchout\Model\Session as PUNSession;
use Punchout2go\Punchout\Helper\Data as DataHelper;

class Data extends Action
{

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var \Punchout2go\Punchout\Model\Session
     */
    protected $punchoutSession;

    /**
     * @var \Punchout2go\Punchout\Helper\Data
     */
    protected $helper;

    public function __construct(
        ActionContext $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        PUNSession $punchoutSession,
        DataHelper $helper
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->punchoutSession = $punchoutSession;
        $this->helper = $helper;
    }

    /**
     * Dispatch request
     *
     * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Json $result */
        $result = $this->resultJsonFactory->create();
        if ($this->punchoutSession->isPunchoutSession()) {
            $punchout_data = $this->punchoutSession->getPunchoutData();
            $punchout_data['is_punchout'] = true;
            $punchout_data['posid'] = $this->punchoutSession->getPunchoutSessionId();
            $punchout_data['config'] = $this->getConfigData();
        } else {
            $punchout_data =  ['is_punchout' => false];
        }
        return $result->setData($punchout_data);
    }

    protected function getConfigData()
    {
        $array =  $this->helper->getConfigData();
        return $array;
    }
}
