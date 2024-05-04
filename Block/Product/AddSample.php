<?php
declare(strict_types=1);

namespace PeachCode\SampleProduct\Block\Product;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Block\Product\View;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductTypes\ConfigInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\Locale\FormatInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Stdlib\StringUtils;
use Magento\Framework\Url\EncoderInterface;
use PeachCode\SampleProduct\Model\Config\Config;

class AddSample extends View
{

    /**
     * @param Context                                  $context
     * @param EncoderInterface                         $urlEncoder
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param StringUtils                              $string
     * @param \Magento\Catalog\Helper\Product          $productHelper
     * @param ConfigInterface                          $productTypeConfig
     * @param FormatInterface                          $localeFormat
     * @param Session                                  $customerSession
     * @param ProductRepositoryInterface               $productRepository
     * @param PriceCurrencyInterface                   $priceCurrency
     * @param Config                                   $config
     * @param array                                    $data
     */
    public function __construct(
        Context $context,
        EncoderInterface $urlEncoder,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        StringUtils $string,
        \Magento\Catalog\Helper\Product $productHelper,
        ConfigInterface $productTypeConfig,
        FormatInterface $localeFormat,
        Session $customerSession,
        ProductRepositoryInterface $productRepository,
        PriceCurrencyInterface $priceCurrency,
        private readonly Config $config,
        array $data = []
    ) {
        $this->priceCurrency = $priceCurrency;
        parent::__construct(
            $context,
            $urlEncoder,
            $jsonEncoder,
            $string,
            $productHelper,
            $productTypeConfig,
            $localeFormat,
            $customerSession,
            $productRepository,
            $priceCurrency,
            $data
        );
    }

    /**
     * @return string
     */
    public function getAddSampleUrl(): string
    {
        return $this->getUrl('sampleproduct/cart/add');
    }

    /**
     * @param Product $product
     *
     * @return mixed|null
     */
    public function isSampleAvailable(Product $product): mixed
    {
        $attributeCode = $this->config->getSampleAttribute();
        return $product->getData($attributeCode);
    }

    /**
     * Get sample price
     *
     * @param Product $product
     *
     * @return mixed
     */
    public function samplePrice(Product $product): mixed
    {
        if($product->getCustomAttribute('sample_product_price')){
            return $this->priceCurrency->format(
                $product->getCustomAttribute('sample_product_price')->getValue()
            );
        }
        return 0;
    }
}
