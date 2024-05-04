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
use Magento\Framework\Message\ManagerInterface;
use PeachCode\SampleProduct\Helper\Logger;
use PeachCode\SampleProduct\Model\Cart;
use PeachCode\SampleProduct\Model\CartFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\ActionInterface;
use PeachCode\SampleProduct\Model\Config\Config;
use PeachCode\SampleProduct\Model\ResourceModel\Cart\CollectionFactory;

class Add implements ActionInterface
{
    /**
     * @param RequestInterface           $request
     * @param ResultFactory              $resultFactory
     * @param RedirectInterface          $redirect
     * @param Logger                     $logger
     * @param ManagerInterface           $messageManager
     * @param CartFactory                $sampleCartFactory
     * @param CollectionFactory          $sampleCartCollectionFactory
     * @param Session                    $customerSession
     * @param Config                     $config
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(
        private readonly RequestInterface $request,
        private readonly ResultFactory $resultFactory,
        private readonly RedirectInterface $redirect,
        private readonly Logger $logger,
        private readonly ManagerInterface $messageManager,
        private readonly CartFactory $sampleCartFactory,
        private readonly CollectionFactory $sampleCartCollectionFactory,
        private readonly Session $customerSession,
        private readonly Config $config,
        private readonly ProductRepositoryInterface $productRepository
    ) {
    }

    /**
     * @return ResultInterface|ResponseInterface|Redirect
     * @throws NotFoundException
     */
    public function execute(): ResultInterface|ResponseInterface|Redirect
    {
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        if (!$this->request->isPost()) {
            throw new NotFoundException(__("Not Found."));
        }

        try {

            if (!$this->customerSession->isLoggedIn()
                || !($customerId
                    = $this->customerSession->getCustomerId())
            ) {
                throw new LocalizedException(__("User not logged in."));
            }

            $post = $this->request->getPostValue();

            $productId = (int)$post['sample_product_id'];

            $product = $this->productRepository->getById($productId);

            $sampleAttributeCode = $this->config->getSampleAttribute();

            if (!$sampleAttributeCode
                || !$product->getCustomAttribute($sampleAttributeCode)
                    ->getValue()
            ) {
                throw new LocalizedException(__('Sample not available for product'
                    .$product->getSku()."."));
            }

            $cart = $this->getCurrentCartOrCreate($customerId);

            /** @var $cart Cart */
            $cart->addCartItem($productId);

            $this->messageManager->addSuccessMessage(__('Sample added to cart'));


        } catch (Exception $e) {
            $this->messageManager->addErrorMessage(__('Could not add sample to cart. '
                .$e->getMessage()));
            $this->logger->log($e->getMessage(), Logger::PeachCode_EXCEPTION);
        }

        $resultRedirect->setUrl($this->redirect->getRefererUrl());

        return $resultRedirect;
    }

    /**
     * @param $customerId
     *
     * @return DataObject|Cart
     */
    private function getCurrentCartOrCreate($customerId): Cart|DataObject
    {
        $collection = $this->sampleCartCollectionFactory->create();

        $collection->addFieldToFilter('customer_id', ['eq' => $customerId]);
        $customerCart = $collection->getFirstItem();

        if ($customerCart->getId()) {
            return $customerCart;
        }

        $customerCart = $this->sampleCartFactory->create();
        $customerCart->setCustomerId($customerId)->save();

        return $customerCart;
    }
}
