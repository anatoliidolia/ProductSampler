<?php
declare(strict_types=1);

namespace PeachCode\SampleProduct\Block\Adminhtml\Order;

use Exception;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Grid\Extended;
use Magento\Backend\Helper\Data;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;
use Magento\Framework\Exception\FileSystemException;
use PeachCode\SampleProduct\Model\ResourceModel\Order\Item\Collection;
use PeachCode\SampleProduct\Model\ResourceModel\Order\Item\CollectionFactory;

class ViewGrid extends Extended
{

    /**
     * @param Registry                                $coreRegistry
     * @param Context $context
     * @param Data            $backendHelper
     * @param CollectionFactory                       $modelCollectionFactory
     * @param array                                   $data
     */
    public function __construct(
        private readonly Registry $coreRegistry,
        private readonly Context $context,
        private readonly Data $backendHelper,
        private readonly CollectionFactory $modelCollectionFactory,
        array $data = []
    ) {
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * Class constructor
     *
     * @return void
     * @throws FileSystemException
     */
    protected function _construct(): void
    {
        parent::_construct();

        $this->setId('sampleOrderItems');
        $this->setDefaultSort('sku');
        $this->setDefaultDir('ASC');
        $this->setTitle(__('Sample Order Items'));
        $this->setSaveParametersInSession(true);
    }

    /**
     * @return string
     * @throws LocalizedException
     */
    public function getMainButtonsHtml(): string
    {
        $html = parent::getMainButtonsHtml();
        $addButton = $this->getLayout()->createBlock('Magento\Backend\Block\Widget\Button')
            ->setData(array(
                'label' => __('ReSend Sample Order Email'),
                'onclick' => 'window.setLocation("'.$this->getUrl('*/*/sendEmail', ['_current' => true, 'id' => $this->getSampleOrder()->getId()]).'")',
                'class'   => 'task'
            ))->toHtml();
        return $html.$addButton;
    }


    /**
     * @return $this
     */
    protected function _prepareCollection(): static
    {
        $collection = $this->modelCollectionFactory->create();

        $collection->addFieldToFilter("order_id",['eq'=>$this->getSampleOrder()->getId()]);

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * @return $this
     * @throws Exception
     */
    protected function _prepareColumns(): static
    {
        $this->addColumn('sku', ['header' => __('SKU'), 'index' => 'sku','filter' => false, 'sortable' => false,'align' => 'center', 'type' => 'text']);
        $this->addColumn('name', ['header' => __('Name'), 'index' => 'name','filter' => false, 'sortable' => false,'align' => 'center', 'type' => 'text']);
        $this->addColumn('quantity', ['header' => __('Quantity'),'filter' => false, 'sortable' => false,'align' => 'center', 'type' => 'renderer','renderer' => '\PeachCode\SampleProduct\Block\Adminhtml\Order\Renderer\QuantityRenderer']);
        $this->addColumn('price', ['header' => __('Price'),'index' => 'price','filter' => false, 'sortable' => false,'align' => 'center', 'type' => 'text']);

        return parent::_prepareColumns();
    }

    /**
     * @return mixed|null
     */
    protected function getSampleOrder(): mixed
    {
        return $this->coreRegistry->registry('current_sample_order');
    }
}
