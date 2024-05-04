<?php
declare(strict_types=1);

namespace PeachCode\SampleProduct\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Cart extends AbstractDb
{
    public const TABLE_NAME = 'peach_code_samples_cart';

    /**
     * @return void
     */
    protected function _construct(): void
    {
        $this->_init(self::TABLE_NAME,'cart_id');
    }
}
