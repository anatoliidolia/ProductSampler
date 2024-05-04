<?php
declare(strict_types=1);

namespace PeachCode\SampleProduct\Model\Cart;

use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use PeachCode\SampleProduct\Model\Api\ConfigInterface;

class Item extends AbstractModel implements IdentityInterface
{

    /**
     * @param Context                     $context
     * @param Registry $registry
     * @param array                       $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        array $data = []
    ){
        parent::__construct($context, $registry, null, null, $data);
    }

    /**
     * @return void
     */
    protected function _construct(): void
    {
        $this->_init('PeachCode\SampleProduct\Model\ResourceModel\Cart\Item');
    }

    /**
     * @return string[]
     */
    public function getIdentities(): array
    {
        return [ConfigInterface::XML_CACHE_TAG . '_' . $this->getId()];
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
}
