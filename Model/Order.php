<?php
declare(strict_types=1);

namespace PeachCode\SampleProduct\Model;

use PeachCode\SampleProduct\Model\Order\ItemFactory;
use PeachCode\SampleProduct\Model\ResourceModel\Cart\Item\Collection;
use PeachCode\SampleProduct\Model\ResourceModel\Order\Item\CollectionFactory;
use Magento\Customer\Model\ResourceModel\CustomerRepository;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use PeachCode\SampleProduct\Model\Api\ConfigInterface;

class Order extends AbstractModel implements  IdentityInterface
{
    protected ItemFactory $samplesOrderItemFactory;

    protected $customerRepository;

    protected $sampleOrderItemCollectionFactory;

    /**
     * @param Context            $context
     * @param Registry           $registry
     * @param ItemFactory        $samplesOrderItemFactory
     * @param CustomerRepository $customerRepository
     * @param CollectionFactory  $sampleOrderItemCollectionFactory
     * @param array              $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ItemFactory $samplesOrderItemFactory,
        CustomerRepository $customerRepository,
        CollectionFactory $sampleOrderItemCollectionFactory,
        array $data = []
    ){
        parent::__construct($context, $registry, null, null, $data);
        $this->samplesOrderItemFactory = $samplesOrderItemFactory;
        $this->customerRepository = $customerRepository;
        $this->sampleOrderItemCollectionFactory = $sampleOrderItemCollectionFactory;

    }

    /**
     * @return void
     */
    protected function _construct(): void
    {
        $this->_init('PeachCode\SampleProduct\Model\ResourceModel\Order');
    }

    /**
     * @return string[]
     */
    public function getIdentities(): array
    {
        return [ConfigInterface::XML_ORDER_CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * @throws NoSuchEntityException
     */
    public function loadById($id): static
    {
        $this->getResource()->load($this, $id);
        if (! $this->getId()) {
            throw new NoSuchEntityException(__('Unable to find sample order with ID "%1"', $id));
        }
        return $this;
    }

    /**
     * @param $samplesCart Cart
     *
     * @return Order
     * @throws LocalizedException
     */
    public function createFromCart(Cart $samplesCart): static
    {

        if ($this->getId()) {
            throw new LocalizedException(__("Cannot overwrite order."));
        }

        $customer = $this->customerRepository->getById($samplesCart->getCustomerId());

        $this->setCustomerId($customer->getId());
        $this->setCustomerEmail($customer->getEmail());


        $customerNumberAttribute = $customer->getCustomAttribute("customer_number");
        if($customerNumberAttribute){
            $this->setCustomerAccountNumber($customerNumberAttribute->getValue());
        }

        $cartItems = $samplesCart->getAllItems();

        if(!($cartItems && $cartItems->getSize())){
            throw new LocalizedException(__("No items in cart."));
        }

        $this->setTotalItems($cartItems->getSize());

        $this->save();

        $orderId = $this->getId();

        try {
            //Add items
            foreach ($cartItems as $cartItem) {
                $orderItem = $this->samplesOrderItemFactory->create();
                $orderItem->createFromCartItem($cartItem,$orderId);
            }
        }catch (\Exception $e){
            //Deleting order if items were unsuccessful
            $this->delete();
            throw new LocalizedException(__($e->getMessage()));
        }

        return $this;
    }

    /**
     * @return ResourceModel\Order\Item\Collection|Collection
     */
    public function getAllItems(): ResourceModel\Order\Item\Collection|Collection
    {
        $collection = $this->sampleOrderItemCollectionFactory->create();

        /** @var $collection Collection */
        $collection->addFieldToFilter('order_id',['eq' => $this->getId()]);
        $collection->load();

        return $collection;
    }
}
