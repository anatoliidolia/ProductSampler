<?php
declare(strict_types=1);

namespace PeachCode\SampleProduct\Model\Config;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use PeachCode\SampleProduct\Model\Api\ConfigInterface;

class Config
{

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig
    ) {
    }

    /**
     * Get custom option
     *
     * @return mixed|int
     */
    public function getCartItemsLimit(): mixed
    {
        return $this->scopeConfig->getValue(ConfigInterface::XML_CART_ITEM_LIMIT, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return mixed
     */
    public function getSampleAttribute(): mixed
    {
        return $this->scopeConfig->getValue(ConfigInterface::XML_PRODUCT_SAMPLE_ATTRIBUTE, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return mixed
     */
    public function getSampleOrdersEmail(): mixed
    {
        return $this->scopeConfig->getValue(ConfigInterface::XML_EMAIL_DESTINATION);
    }

    /**
     * @return mixed
     */
    public function getSampleOrdersErrorEmail(): mixed
    {
        return $this->scopeConfig->getValue(ConfigInterface::XML_EMAIL_ERROR);
    }
}
