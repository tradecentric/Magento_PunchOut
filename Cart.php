<?php
namespace Punchout2go\Punchout;

class Cart extends \Magento\Framework\DataObject
{
    /** @var \Magento\Framework\App\Action\Context  */
    protected $context;
    /** @var Cart\Items  */
    protected $items;
    /** @var  \Punchout2go\Punchout\Model\Sesssion */
    protected $punchout_session;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        Cart\Items $items,
        array $data = []
    ) {
        $this->context = $context;
        $this->items = $items;
        parent::__construct($data);
    }

    /**
     * @return \Magento\Framework\App\Action\Context
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * @param \Magento\Framework\App\Action\Context $context
     */
    public function setContext($context)
    {
        $this->context = $context;
    }

    /**
     * @return Cart\Items
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * @param Cart\Items $items
     */
    public function setItems($items)
    {
        $this->items = $items;
    }

    /**
     * @return Model\Sesssion
     */
    public function getPunchoutSession()
    {
        return $this->punchout_session;
    }

    /**
     * @param Model\Sesssion $punchout_session
     */
    public function setPunchoutSession($punchout_session)
    {
        $this->punchout_session = $punchout_session;
    }

    public function toArray(array $keys = [])
    {
        $data = parent::toArray($keys);
        $items = $this->getItems();
        foreach ($items as $item) {
            $data['items'][] = $item->toArray();
        }
        return $data;
    }
}
