<?php

declare(strict_types=1);

namespace PeachCode\SampleProduct\Controller\Referrer;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Action;
use PeachCode\SampleProduct\Helper\Logger;
use PeachCode\SampleProduct\Model\Cart;
use PeachCode\SampleProduct\Model\CartFactory;
use PeachCode\SampleProduct\Model\Config\Config;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use PeachCode\SampleProduct\Model\ResourceModel\Cart\CollectionFactory;

class AddToCart extends Action
{
    /**
     * @param Context                    $context
     * @param Logger                     $logger
     * @param CartFactory                $sampleCartFactory
     * @param CollectionFactory          $sampleCartCollectionFactory
     * @param Session                    $customerSession
     * @param Config                     $config
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(
        Context $context,
        private readonly Logger $logger,
        private readonly CartFactory $sampleCartFactory,
        private readonly CollectionFactory $sampleCartCollectionFactory,
        private readonly Session $customerSession,
        private readonly Config $config,
        private readonly ProductRepositoryInterface $productRepository
    ) {
        parent::__construct($context);
    }

    /**
     * @throws NotFoundException
     */
    public function execute()
    {
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        if ($this->getRequest()->isPost()) {
            throw new NotFoundException(__('Not Found.'));
        }

        try {

            if (!$this->customerSession->isLoggedIn() || !($customerId = $this->customerSession->getCustomerId())) {
                throw new LocalizedException(__('User not logged in.'));
            }

            $sampleString = $this->getRequest()->getParam('samples');

            if(!$sampleString || trim($sampleString) == ""){
                $resultRedirect->setUrl($this->_url->getUrl('sampleproduct/cart/view'));
                return $resultRedirect;
            }

            $samplesArray = explode(";", $sampleString);

            foreach ($samplesArray as $sampleCode) {

                try {

                    try {
                        $product = $this->productRepository->get($sampleCode);
                    } catch (NoSuchEntityException $e) {
                        $product = $this->productRepository->get("$sampleCode-GROUPED");
                    }

                    $sampleAttributeCode = $this->config->getSampleAttribute();

                    if (!$sampleAttributeCode || !$product->getCustomAttribute($sampleAttributeCode)->getValue()) {
                        throw new LocalizedException(__('Sample not available for product ' . $product->getSku()));
                    }

                    $cart = $this->getCurrentCartOrCreate($customerId);

                    $cart->addCartItem($product->getId());

                    $this->messageManager->addSuccessMessage(__("Sample for code $sampleCode added to cart"));

                } catch (NoSuchEntityException $e) {
                    $this->messageManager->addErrorMessage(__('Could not find sample for code: ' . $sampleCode));
                } catch (LocalizedException $e) {
                    $this->messageManager->addErrorMessage(__('Sample for code $sampleCode not added. ' . $e->getMessage()));
                }

            }

        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('Could not add sample to cart. ' . $e->getMessage()));
            $this->logger->log($e->getMessage(), Logger::PeachCode_EXCEPTION);
        }

        $resultRedirect->setUrl($this->_url->getUrl('sampleproduct/cart/view'));
        return $resultRedirect;

    }

    /**
     * @param $customerId
     *
     * @return Cart
     */
    private function getCurrentCartOrCreate($customerId): Cart
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
