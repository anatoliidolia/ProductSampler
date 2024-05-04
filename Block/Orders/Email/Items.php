<?php
declare(strict_types=1);

namespace PeachCode\SampleProduct\Block\Orders\Email;

use PeachCode\SampleProduct\Model\ResourceModel\Cart\Item\Collection;
use PeachCode\SampleProduct\Model\ResourceModel\Order\Item\CollectionFactory;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context as TemplateContext;
use PeachCode\SampleProduct\Model\Api\ConfigInterface;

/**
 * Data for Email content
 */
class Items extends Template
{

    /**
     * @param TemplateContext $context
     * @param CollectionFactory $sampleOrderItemCollectionFactory
     * @param array $data
     */
    public function __construct(
        private readonly TemplateContext $context,
        private readonly CollectionFactory $sampleOrderItemCollectionFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * Get current order id from request
     *
     * @return int
     */
    public function getOrder(): int
    {
        if (!empty($_SESSION[ConfigInterface::XML_SAMPLE_ORDER_ID])){
            return (int)$_SESSION[ConfigInterface::XML_SAMPLE_ORDER_ID];
        }
        return (int)$this->getRequest()->getParam('id');
    }


    /**
     * @param $order
     * @return Collection|\PeachCode\SampleProduct\Model\ResourceModel\Order\Item\Collection
     */
    public function getAllItems($order): \PeachCode\SampleProduct\Model\ResourceModel\Order\Item\Collection|Collection
    {

        $collection = $this->sampleOrderItemCollectionFactory->create();

        /** @var Collection $collection */
        $collection->addFieldToFilter('order_id',['eq' => $order]);
        $collection->load();

        return $collection;
    }
}
