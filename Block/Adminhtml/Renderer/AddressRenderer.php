<?php
declare(strict_types=1);

namespace PeachCode\SampleProduct\Block\Adminhtml\Renderer;

use Magento\Backend\Block\Context;
use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magento\Framework\DataObject;

class AddressRenderer extends AbstractRenderer
{

    /**
     * @param Context $context
     * @param array   $data
     */
    public function __construct(
        private readonly Context $context,
        array $data = []
    ) {

        parent::__construct($context, $data);
        $this->_authorization = $context->getAuthorization();
    }

    /**
     * @param DataObject $row
     *
     * @return array|mixed|string|null
     */
    public function render(DataObject $row): mixed
    {
        return $row->getData('html_address');
    }
}
