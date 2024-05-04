<?php
declare(strict_types=1);

namespace PeachCode\SampleProduct\Controller\Adminhtml\Orders;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Backend\App\Action;
use Magento\Framework\Registry;
use PeachCode\SampleProduct\Model\OrderFactory;
abstract class AbstractAction extends Action {

    /**
     * @param Context      $context
     * @param Registry     $coreRegistry
     * @param OrderFactory $sampleOrderFactory
     */
    public function __construct(
        Context $context,
        public readonly Registry $coreRegistry,
        private readonly OrderFactory $sampleOrderFactory,
    ) {
        parent::__construct($context);
    }

    /**
     * TODO: need to fix this function
     *
     * @return void
     * @throws NoSuchEntityException
     */
    public function registerCurrentSampleOrder(): void
    {
        if($id = filter_var($this->getRequest()->getParam('id'), FILTER_VALIDATE_INT)){
            $this->coreRegistry->register('current_sample_order', $this->sampleOrderFactory->create()->loadById($id));
        }
    }
}
