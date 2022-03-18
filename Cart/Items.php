<?php

namespace Punchout2go\Punchout\Cart;

class Items implements \Iterator
{
    /** @var int */
    protected $position = 0;
    /** @var array */
    protected $items = [];

    /**
     * @param Item|array $data
     */
    public function addItem($data)
    {
        if (is_array($data)) {
            $this->getNewItem()->addData($data);
        } else {
            $this->items[] = $data;
        }
    }

    /**
     * get an empty item in the next position
     *
     * @return Item
     */
    public function getNewItem()
    {
        $nextId = count($this->items);

        // TODO: Use "Magento Way" to instantiate/inject new objects, rather than directly.
        $this->items[] = new Item();

        return $this->items[$nextId];
    }

    /**
     * @return array
     */
    public function getData()
    {
        $data = [];
        $this->rewind();
        foreach ($this as $item) {
            $data[] = $item->getData();
        }

        return $data;
    }

    public function current()
    {
        return $this->items[$this->position];
    }

    public function next()
    {
        $this->position++;
    }

    public function key()
    {
        return $this->position;
    }

    public function valid()
    {
        if (isset($this->items[$this->position])) {
            return true;
        }

        return false;
    }

    public function rewind()
    {
        $this->position = 0;
    }
}
