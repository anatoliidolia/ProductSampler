<?php
declare(strict_types=1);

namespace PeachCode\SampleProduct\Controller\Adminhtml\Orders;

use Exception;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action;
use Magento\Framework\Exception\NoSuchEntityException;
use PeachCode\SampleProduct\Helper\EmailHelper;
use PeachCode\SampleProduct\Helper\Logger;
use PeachCode\SampleProduct\Model\Order;
use PeachCode\SampleProduct\Model\OrderFactory;

class SendEmail extends Action
{

    /**
     * @param Context      $context
     * @param OrderFactory $sampleOrderFactory
     * @param Logger       $logger
     * @param EmailHelper  $emailHelper
     */
    public function __construct(
        Context $context,
        private readonly OrderFactory $sampleOrderFactory,
        private readonly Logger $logger,
        private readonly EmailHelper $emailHelper
    ) {
        parent::__construct($context);
    }

    public function execute()
    {
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        try {
            $order = $this->getCurrentSampleOrder();
            $emailSent = $this->emailHelper->sendEmail($order);

            $order->setEmailSent($emailSent);
            $order->save();

            if ($emailSent) {
                $this->messageManager->addSuccessMessage(__("Success. Sample Order Email sent."));
            } else {
                $this->messageManager->addErrorMessage(__('Could not send email. Error message sent to the specified email in "Configuration -> PeachCode -> SampleProduct Settings -> Email Settings".'));
            }

        } catch (Exception $e) {
            $this->messageManager->addErrorMessage(__('Could not send email. '.$e->getMessage()));
            $this->logger->log($e->getMessage(), Logger::PeachCode_EXCEPTION);
        }
        $resultRedirect->setUrl($this->_redirect->getRefererUrl());

        return $resultRedirect;
    }

    /**
     * Get current Sample Order from params in URL
     *
     * @return Order|null
     * @throws NoSuchEntityException
     */
    private function getCurrentSampleOrder(): ?Order
    {
        if ($id = filter_var($this->getRequest()->getParam('id'),
            FILTER_VALIDATE_INT)
        ) {
            return $this->sampleOrderFactory->create()->loadById($id);
        }
        return null;
    }
}
