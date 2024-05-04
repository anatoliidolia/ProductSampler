<?php
declare(strict_types=1);

namespace PeachCode\SampleProduct\Block\Orders;

use Magento\Customer\Model\Session;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use PeachCode\SampleProduct\Model\ResourceModel\Order\CollectionFactory;

/**
 * Sales order history block
 */
class Recent extends Template
{

    /**
     * @param Context                                                              $context
     * @param CollectionFactory $orderCollectionFactory
     * @param Session                                                              $customerSession
     * @param array                                                                $data
     */
    public function __construct(
        private readonly Context $context,
        private readonly CollectionFactory $orderCollectionFactory,
        private readonly Session $customerSession,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_isScopePrivate = true;
    }

    /**
     * @return void
     */
    protected function _construct(): void
    {
        parent::_construct();
        $orders = $this->orderCollectionFactory->create()->addFieldToFilter(
            'customer_id',
            $this->customerSession->getCustomerId()
        )->setOrder(
            'created_at',
            'desc'
        )->setPageSize(
            '5'
        )->load();


        $this->setOrders($orders);
    }

    /**
     * @param object $order
     * @return string
     */
    public function getViewUrl($order): string
    {
        return $this->getUrl('sampleproduct/order/view', ['sample_order_id' => $order->getId()]);
    }
    /**
     * @return string
     */
    protected function _toHtml(): string
    {
        if ($this->getOrders()->getSize() > 0) {
            return parent::_toHtml();
        }
        return '';
    }
}
