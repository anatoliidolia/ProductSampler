<?php
declare(strict_types=1);

namespace PeachCode\SampleProduct\Controller\Adminhtml\Orders;

use Magento\Backend\App\Action;

class Index extends Action
{
    /**
     * @return void
     */
    public function execute(): void
    {
        $this->_initAction();
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Sample Product Orders'));
        $this->_view->renderLayout();
    }

    /**
     * @return $this
     */
    protected function _initAction(): static
    {
        $this->_view->loadLayout();
        return $this;
    }
}
