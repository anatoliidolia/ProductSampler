<?php
declare(strict_types=1);

namespace PeachCode\SampleProduct\Model\Order;

use Magento\Catalog\Model\Product;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Catalog\Api\Data\ProductInterface;
use PeachCode\SampleProduct\Model\Api\ConfigInterface;

class Item extends AbstractModel implements IdentityInterface
{

    /**
     * @param Context $context
     * @param Registry      $registry
     * @param ProductRepositoryInterface       $productRepository
     * @param array                            $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        private readonly ProductRepositoryInterface $productRepository,
        array $data = []
    )
    {
        parent::__construct($context, $registry, null, null, $data);
    }

    /**
     * @return void
     */
    protected function _construct(): void
    {
        $this->_init('PeachCode\SampleProduct\Model\ResourceModel\Order\Item');
    }

    /**
     * @return string[]
     */
    public function getIdentities(): array
    {
        return [ConfigInterface::XML_ORDER_CACHE_TAG_ITEM . '_' . $this->getId()];
    }

    /**
     * @param $id
     *
     * @return $this
     * @throws NoSuchEntityException
     */
    public function loadById($id): static
    {
        $this->getResource()->load($this, $id);
        if (!$this->getId()) {
            throw new NoSuchEntityException(__('Unable to find sample cart item with ID "%1"', $id));
        }
        return $this;
    }

    /**
     * @param $cartItem
     * @param $orderId
     *
     * @return $this
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function createFromCartItem($cartItem, $orderId): static
    {

        /** @var $cartItem \PeachCode\SampleProduct\Model\Cart\Item */
        if ($this->getId()) {
            throw new LocalizedException(__('Cannot overwrite order.'));
        }

        $this->setOrderId($orderId);

        $product = $this->productRepository->getById($cartItem->getProductId());

        $sampleSku = $this->getFormattedSku($product);

        $this->setPrice('Free');

        if($product->getCustomAttribute(ConfigInterface::XML_SAMPLE_PRODUCT_PRICE)){
            $this->setPrice($product->getCustomAttribute(ConfigInterface::XML_SAMPLE_PRODUCT_PRICE)->getValue());
        }

        $this->setSku($sampleSku);
        $this->setName($product->getName());

        $this->save();
        return $this;
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
}
