<?php
declare(strict_types=1);

namespace PeachCode\SampleProduct\Model;

use Exception;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use PeachCode\SampleProduct\Model\Cart\ItemFactory;
use PeachCode\SampleProduct\Model\Config\Config;
use PeachCode\SampleProduct\Model\ResourceModel\Cart\Item\CollectionFactory;
use PeachCode\SampleProduct\Model\Api\ConfigInterface;

class Cart extends AbstractModel implements  IdentityInterface
{
    protected Cart\ItemFactory $sampleCartItemFactory;

    protected ResourceModel\Cart\Item\CollectionFactory $sampleCartItemCollectionFactory;

    /**
     * @return void
     */
    protected function _construct(): void
    {
        $this->_init('PeachCode\SampleProduct\Model\ResourceModel\Cart');
    }

    /**
     * @param Context           $context
     * @param Registry          $registry
     * @param ItemFactory       $sampleCartItemFactory
     * @param CollectionFactory $sampleCartItemCollectionFactory
     * @param Config            $config
     * @param array             $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ItemFactory $sampleCartItemFactory,
        CollectionFactory $sampleCartItemCollectionFactory,
        private readonly Config $config,
        $data = []
    )
    {
        parent::__construct($context, $registry, null, null, $data);
        $this->sampleCartItemFactory = $sampleCartItemFactory;
        $this->sampleCartItemCollectionFactory = $sampleCartItemCollectionFactory;
    }

    /**
     * @return string[]
     */
    public function getIdentities(): array
    {
        return [ConfigInterface::XML_CART_CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * @param $productId
     *
     * @return void
     * @throws LocalizedException
     * @throws Exception
     */
    public function addCartItem($productId): void
    {

        if($this->getAllItems()->getSize() == $this->getCartItemsLimit()){
            throw new LocalizedException(__("Samples cart is full."));
        }

        $cartItem = $this->sampleCartItemFactory->create();
        $cartItem->setCartId($this->getId());
        $cartItem->setProductId($productId);
        $cartItem->save();

        $this->save();
    }

    /**
     * @return int|mixed
     */
    private function getCartItemsLimit(){
        return $this->config->getCartItemsLimit();
    }

    /**
     * @return ResourceModel\Cart\Item\Collection
     */
    public function getAllItems(){

        $collection = $this->sampleCartItemCollectionFactory->create();

        $collection->addFieldToFilter('cart_id',['eq' => $this->getId()]);
        $collection->load();

        return $collection;
    }

    /**
     * @param $id
     *
     * @return $this
     * @throws NoSuchEntityException
     */
    public function loadById($id)
    {
        $this->getResource()->load($this, $id);
        if (! $this->getId()) {
            throw new NoSuchEntityException(__('Unable to find sample cart with ID "%1"', $id));
        }
        return $this;
    }
}
