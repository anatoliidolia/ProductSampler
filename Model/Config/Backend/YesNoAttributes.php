<?php
declare(strict_types=1);

namespace PeachCode\SampleProduct\Model\Config\Backend;

use Magento\Catalog\Model\Resource\Product\Attribute\CollectionFactory;
use Magento\Directory\Model\Resource\Country\Collection;
use Magento\Framework\Option\ArrayInterface;

class YesNoAttributes implements ArrayInterface
{

    /**
     * @var CollectionFactory
     */
    protected \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory|CollectionFactory $_collectionFactory;

    /**
     * @param \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory $collectionFactory
     */
    public function __construct(\Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory $collectionFactory)
    {
        $this->_collectionFactory = $collectionFactory;
    }

    /**
     * @return array
     */
    public function toOptionArray(): array
    {
        $options = array();
        $collection = $this->_collectionFactory->create()->addVisibleFilter();

        foreach($collection as $item)
        {
            //Boolean type only
            if($item->getFrontendInput() == "boolean") {
                $options[] = array('value' => $item->getAttributeCode(), 'label' => $item->getAttributeCode());
            }
        }

        return $options;
    }

}
