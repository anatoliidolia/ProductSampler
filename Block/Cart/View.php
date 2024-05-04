<?php
declare(strict_types=1);

namespace PeachCode\SampleProduct\Block\Cart;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Block\Product\Image;
use Magento\Catalog\Block\Product\ImageBuilder;
use Magento\Catalog\Model\Product;
use Magento\Customer\Model\Session;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use PeachCode\SampleProduct\Block\Product\AddSample;
use PeachCode\SampleProduct\Helper\Logger;
use PeachCode\SampleProduct\Model\Cart;
use PeachCode\SampleProduct\Model\ResourceModel\Cart\CollectionFactory;
use PeachCode\SampleProduct\Model\ResourceModel\Cart\Item\Collection;

class View extends Template
{
    /**
     * @param AddSample                  $productAddSample
     * @param Context                    $context
     * @param Logger                     $logger
     * @param Session                    $customerSession
     * @param CollectionFactory          $sampleCartCollectionFactory
     * @param ProductRepositoryInterface $productRepository
     * @param ImageBuilder               $imageBuilder
     * @param array                      $data
     */
    public function __construct(
        private readonly \PeachCode\SampleProduct\Block\Product\AddSample $productAddSample,
        private readonly Template\Context $context,
        private readonly Logger $logger,
        private readonly Session $customerSession,
        private readonly CollectionFactory $sampleCartCollectionFactory,
        private readonly ProductRepositoryInterface $productRepository,
        private readonly ImageBuilder $imageBuilder,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * @return Collection|null
     */
    public function getCartItems(
    ): ?Collection
    {
        if (!$this->customerSession->isLoggedIn() || !($customerId = $this->customerSession->getCustomerId())
        ) {
            return null;
        }

        if ($cart = $this->getCurrentCart($customerId)) {
            return $cart->getAllItems();
        }

        return null;
    }

    /**
     * @param $productId
     *
     * @return ProductInterface|null
     */
    public function getProductById($productId): ?ProductInterface
    {
        $product = null;

        try {
            $product = $this->productRepository->getById($productId);
        } catch (NoSuchEntityException $e) {
            $this->logger->log($e->getMessage(), Logger::PeachCode_EXCEPTION);
        }

        return $product;
    }


    /**
     * @param $customerId
     *
     * @return Cart|DataObject
     */
    protected function getCurrentCart($customerId): Cart|DataObject
    {
        $collection = $this->sampleCartCollectionFactory->create();

        $collection->addFieldToFilter('customer_id', ['eq' => $customerId]);

        return $collection->getFirstItem();

    }

    /**
     * @return string
     */
    public function getRemoveUrl(): string
    {
        return $this->getUrl('sampleproduct/cart/remove');
    }

    /**
     * @param $product
     *
     * @return string|array
     */
    public function formatSku($product): string|array
    {
        return $this->getFormattedSku($product);
    }

    /**
     * @param $product Product|ProductInterface
     *
     * @return array|string|string[]
     */
    private function getFormattedSku(ProductInterface|Product $product
    ): array|string {
        $type = $product->getTypeId();
        $sku = $product->getSku();

        if ($type == "grouped") {
            $sku = str_replace("-GROUPED", "", $sku);
        }

        return $sku;
    }

    /**
     * Retrieve product image
     *
     * @param Product $product
     * @param string  $imageId
     * @param array   $attributes
     *
     * @return Image
     */
    public function getImage(
        Product $product,
        string $imageId,
        array $attributes = []
    ): Image {
        return $this->imageBuilder->setProduct($product)
            ->setImageId($imageId)
            ->setAttributes($attributes)
            ->create();
    }

    /**
     * @return string
     */
    public function getPostUrl(): string
    {
        return $this->getUrl('sampleproduct/cart/submit');
    }

    /**
     * Get sample price
     *
     * @param Product $product
     *
     * @return mixed
     */
    public function getSamplePrice(Product $product): mixed
    {
        return $this->productAddSample->samplePrice($product);
    }
}
