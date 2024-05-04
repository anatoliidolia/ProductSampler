<?php
declare(strict_types=1);

namespace PeachCode\SampleProduct\Block\Adminhtml;

use Exception;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Grid\Extended;
use Magento\Backend\Helper\Data;
use Magento\Catalog\Model\Product;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\FileSystemException;
use PeachCode\SampleProduct\Model\ResourceModel\Order\CollectionFactory;

class OrderGrid extends Extended
{
    /**
     * @param Context           $context
     * @param Data              $backendHelper
     * @param CollectionFactory $modelCollectionFactory
     * @param array             $data
     */
    public function __construct(
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

        $this->setId('sampleOrders');
        $this->setDefaultSort('created_at');
        $this->setDefaultDir('DESC');
        $this->setTitle(__('Sample Orders'));
        $this->setSaveParametersInSession(true);
    }

    /**
     * @return OrderGrid
     */
    protected function _prepareCollection(): OrderGrid
    {
        $collection = $this->modelCollectionFactory->create();

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * @return OrderGrid
     * @throws Exception
     */
    protected function _prepareColumns(): OrderGrid
    {
        $this->addColumn('order_id', ['header' => __('Order ID'), 'index' => 'order_id','align' => 'center', 'type' => 'number']);
        $this->addColumn('customer_email',
            ['header' => __('Customer'),
                'index' => 'customer_email',
                'align' => 'center',
                'type' => 'renderer',
                'renderer' => '\PeachCode\SampleProduct\Block\Adminhtml\Renderer\CustomerRenderer']);
        $this->addColumn('total_items', ['header' => __('Total Items'), 'index' => 'total_items','align' => 'center', 'type' => 'number']);
        $this->addColumn('html_address',
            ['header' => __('Delivery Address'),
                'index' => 'html_address',
                'align' => 'left',
                'type' => 'renderer',
                'renderer' => '\PeachCode\SampleProduct\Block\Adminhtml\Renderer\AddressRenderer']);
        $this->addColumn('email_sent',
            ['header' => __('Email Sent'),
                'index' => 'email_sent',
                'align' => 'center',
                'type' => 'options',
                'options' => $this->_getBooleanOptions()]
        );
        $this->addColumn('created_at', ['header' => __('Created At'), 'index' => 'created_at','align' => 'center', 'type' => 'datetime']);

        return parent::_prepareColumns();
    }

    /**
     * @param Product|DataObject $item
     *
     * @return string
     */
    public function getRowUrl($item): string
    {

        return $this->getUrl('sampleproduct/orders/view', ['id' => $item->getId()]);

    }

    /**
     * @return string
     */
    public function getGridUrl(): string
    {
        return $this->getUrl('*/*/index');
    }

    /**
     * @return string[]
     */
    private function _getBooleanOptions(): array
    {
        return [
            0 => "No",
            1 => "Yes"
        ];
    }
}
