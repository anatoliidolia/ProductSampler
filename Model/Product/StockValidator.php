<?php
declare(strict_types=1);

namespace PeachCode\SampleProduct\Model\Product;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use PeachCode\SampleProduct\Model\Config\Config;

class StockValidator{

    /**
     * @param Config                     $config
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(
        private readonly Config $config,
        private readonly  ProductRepositoryInterface $productRepository)
    {
    }

    /**
     * Stock validator
     *
     * @param $productID
     *
     * @return bool
     * @throws NoSuchEntityException
     */
    public function checkIfSampleIsAvailable($productID): bool
    {
        $product = $this->productRepository->getById($productID);

        $sampleAttributeCode = $this->config->getSampleAttribute();

        if ($sampleAttributeCode && $product->getCustomAttribute($sampleAttributeCode)->getValue()) {
            return true;
        }

        return false;
    }
}
