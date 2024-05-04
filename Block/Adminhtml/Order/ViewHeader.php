<?php
declare(strict_types=1);

namespace PeachCode\SampleProduct\Block\Adminhtml\Order;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context as TemplateContext;
use Magento\Framework\Registry;
use PeachCode\SampleProduct\Model\Order;

class ViewHeader extends Template
{

    /**
     * @param TemplateContext $context
     * @param Registry $registry
     * @param array $data
     */
    public function __construct(
        private readonly TemplateContext $context,
        private readonly  Registry $registry,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * @return void
     */
    protected function _prepareLayout(): void
    {
        $this->pageConfig->getTitle()->set(__('Sample Order # %1', $this->getOrder()->getId()));
    }

    /**
     * Retrieve current order model instance
     *
     * @return Order
     */
    public function getOrder(): Order
    {
        return $this->registry->registry('current_sample_order');
    }
}
