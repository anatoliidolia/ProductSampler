<?php
declare(strict_types=1);

namespace PeachCode\SampleProduct\Controller\Adminhtml\Orders;

use Exception;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;

class View extends AbstractAction
{

    /**
     * Prepare view parameters
     *
     * @return ResponseInterface|Redirect|ResultInterface|void
     */
    public function execute(){

        try {
            $this->registerCurrentSampleOrder();
            $this->_view->loadLayout();

            $sampleOrder = $this->coreRegistry->registry('current_sample_order');
            $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Sample Order #%1', $sampleOrder->getId()));

            $this->_view->renderLayout();

        }catch(Exception $e){
            $this->messageManager->addErrorMessage(__('An error occurred : '.$e->getMessage()));
            return $this->resultRedirectFactory->create()->setPath('sampleproduct/orders/index');
        }
    }
}
