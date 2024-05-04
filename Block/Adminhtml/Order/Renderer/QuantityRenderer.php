<?php
declare(strict_types=1);

namespace PeachCode\SampleProduct\Block\Adminhtml\Order\Renderer;

use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magento\Framework\DataObject;

class QuantityRenderer extends AbstractRenderer
{
    /**
     * @param DataObject $row
     *
     * @return int
     */
    public function render(DataObject $row): int
    {
        return 1;
    }
}
