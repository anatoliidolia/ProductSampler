<?php
declare(strict_types=1);

namespace PeachCode\SampleProduct\Controller\Cart;

use Exception;
use Magento\Customer\Model\Session;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\DataObject;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\Message\ManagerInterface;
use PeachCode\SampleProduct\Helper\Logger;
use PeachCode\SampleProduct\Model\Cart\ItemFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Controller\ResultFactory;
use PeachCode\SampleProduct\Model\ResourceModel\Cart\CollectionFactory;

class Remove implements ActionInterface
{

    /**
     * @param RequestInterface  $request
     * @param ResultFactory     $resultFactory
     * @param ManagerInterface  $messageManager
     * @param RedirectInterface $redirect
     * @param Logger            $logger
     * @param ItemFactory       $sampleCartItemFactory
     * @param CollectionFactory $sampleCartCollectionFactory
     * @param Session           $customerSession
     */
    public function __construct(
        private readonly RequestInterface $request,
        private readonly ResultFactory $resultFactory,
        private readonly ManagerInterface $messageManager,
        private readonly RedirectInterface $redirect,
        private readonly Logger $logger,
        private readonly ItemFactory $sampleCartItemFactory,
        private readonly CollectionFactory $sampleCartCollectionFactory,
        private readonly Session $customerSession
    ) {
    }

    /**
     * @return ResultInterface|ResponseInterface|Redirect
     * @throws NotFoundException
     */
    public function execute(): ResultInterface|ResponseInterface|Redirect
    {
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        if(!$this->request->isPost()){
            throw new NotFoundException(__('Not Found.'));
        }

        try {
            if(!$this->customerSession->isLoggedIn() || !($customerId = $this->customerSession->getCustomerId())){
                throw new LocalizedException(__("User not logged in."));
            }

            $post = $this->request->getPostValue();

            $cartItemId = (int)$post['sample_item_id'];

            $cartItem = $this->sampleCartItemFactory->create();

            $cartItem->loadById($cartItemId);

            $currentCart = $this->getCurrentCart($customerId);

            if(!$currentCart->getId()){
                throw new LocalizedException(__('Samples cart not found.'));
            }

            if($cartItem->getCartId() !== $currentCart->getId()){
                throw new LocalizedException(__('Not allowed.'));
            }

            $cartItem->delete();

            $this->messageManager->addSuccessMessage(__('Product sample removed from cart'));

        } catch (Exception $e) {
            $this->messageManager->addErrorMessage(__('Could not remove sample from cart. '.$e->getMessage()));
            $this->logger->log($e->getMessage(), Logger::PeachCode_EXCEPTION);
        }

        $resultRedirect->setUrl($this->redirect->getRefererUrl());
        return $resultRedirect;
    }

    /**
     * @param $customerId
     *
     * @return DataObject
     */
    private function getCurrentCart($customerId): DataObject
    {
        $collection = $this->sampleCartCollectionFactory->create();

        $collection->addFieldToFilter('customer_id',['eq' => $customerId]);

        return $collection->getFirstItem();
    }
}
