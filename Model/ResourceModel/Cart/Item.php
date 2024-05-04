<?php
declare(strict_types=1);

namespace PeachCode\SampleProduct\Model\ResourceModel\Cart;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Item extends AbstractDb
{
    public const TABLE_NAME = 'peach_code_samples_cart_item';

    /**
     * @return void
     */
    protected function _construct(): void
    {
        $this->_init(self::TABLE_NAME,'cart_item_id');
    }
}
