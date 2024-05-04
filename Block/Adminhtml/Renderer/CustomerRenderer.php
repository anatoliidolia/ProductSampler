<?php
declare(strict_types=1);

namespace PeachCode\SampleProduct\Block\Adminhtml\Renderer;

use Magento\Backend\Block\Context;
use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magento\Customer\Model\ResourceModel\CustomerRepository;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

class CustomerRenderer extends AbstractRenderer
{

    /**
     * @param Context            $context
     * @param CustomerRepository $customerRepository
     * @param array              $data
     */
    public function __construct(
        private readonly Context $context,
        private readonly CustomerRepository $customerRepository,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_authorization = $context->getAuthorization();
    }

    /**
     * @param DataObject $row
     *
     * @return array|mixed|string|null
     * @throws LocalizedException
     */
    public function render(DataObject $row): mixed
    {

        $html = $row->getData('customer_email');

        try {
            $this->customerRepository->getById($row->getData('customer_id'));
            $url = $this->getUrl('customer/index/edit',
                ['id' => $row->getData('customer_id')]);
            $html = '<a href="'.$url.'">'.$row->getData('customer_email')
                .'</a>';

        } catch (NoSuchEntityException) {
        }

        return $html;
    }
}
