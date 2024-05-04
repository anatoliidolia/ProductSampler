<?php
declare(strict_types=1);

namespace PeachCode\SampleProduct\Model\ResourceModel\Order\Item;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * @return void
     */
    protected function _construct(): void
    {
        $this->_init('PeachCode\SampleProduct\Model\Order\Item', 'PeachCode\SampleProduct\Model\ResourceModel\Order\Item');
    }
}
