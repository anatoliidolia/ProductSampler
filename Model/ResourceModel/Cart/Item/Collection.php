<?php
declare(strict_types=1);

namespace PeachCode\SampleProduct\Model\ResourceModel\Cart\Item;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{

    /**
     * @return void
     */
    protected function _construct(): void
    {
        $this->_init('PeachCode\SampleProduct\Model\Cart\Item', 'PeachCode\SampleProduct\Model\ResourceModel\Cart\Item');
    }
}
