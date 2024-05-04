<?php
declare(strict_types=1);

namespace PeachCode\SampleProduct\Model\Api;

interface ConfigInterface
{
    public const XML_CART_ITEM_LIMIT = 'sampleproduct/cart/itemlimit';
    public const XML_PRODUCT_SAMPLE_ATTRIBUTE = 'sampleproduct/product/sampleattribute';
    public const XML_EMAIL_DESTINATION = 'sampleproduct/email/destination';
    public const XML_EMAIL_ERROR = 'sampleproduct/email/error';
    public const XML_CACHE_TAG = 'peach_code_samples_cart_item';
    public const XML_ORDER_CACHE_TAG_ITEM = 'peach_code_samples_order_item';

    public const XML_ORDER_CACHE_TAG = 'peach_code_samples_order';

    public const XML_SAMPLE_PRODUCT_PRICE = 'sample_product_price';

    public const XML_CART_CACHE_TAG = 'peach_code_samples_cart';

    public const XML_SAMPLE_ORDER_ID = 'sampleOrderId';
}
