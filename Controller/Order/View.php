<?php
declare(strict_types=1);

namespace PeachCode\SampleProduct\Controller\Order;

use Magento\Customer\Model\Session;
use Magento\Framework\App\RequestInterface;

class View extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Sales\Controller\AbstractController\OrderLoaderInterface
     */
    protected $orderLoader;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    protected $customerSession;

    protected $orderFactory;

    protected $coreRegistry;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        Session $customerSession,
        \PeachCode\SampleProduct\Model\OrderFactory $orderFactory,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->customerSession = $customerSession;
        $this->orderFactory = $orderFactory;
        $this->coreRegistry = $coreRegistry;
        parent::__construct($context);
    }

    /**
     * Order view page
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $result = $this->loadValidOrder();
        if ($result instanceof \Magento\Framework\Controller\ResultInterface) {
            return $result;
        }

        /** @var \Magento\Framework\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();

        return $resultPage;
    }


    /**
     * Try to load valid order by $_POST or $_COOKIE
     *
     * @param RequestInterface $request
     * @return \Magento\Framework\Controller\Result\Redirect|bool
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function loadValidOrder()
    {
        if (!$this->customerSession->isLoggedIn()) {
            return $this->resultRedirectFactory->create()->setPath('/');
        }

        $params = $this->getRequest()->getParams();

        /** @var $order \PeachCode\SampleProduct\Model\Order */
        $order = $this->orderFactory->create();


        if (!empty($params) && isset($params['sample_order_id'])) {

            $orderId = $params['sample_order_id'];


            $order = $order->loadById($orderId);

            if ($order->getId() && $order->getCustomerId() == $this->customerSession->getCustomerId()) {
                $this->coreRegistry->register('current_sample_order', $order);
                return true;
            }


        }

        $this->messageManager->addError(__('You entered incorrect data. Please try again.'));
        return $this->resultRedirectFactory->create()->setPath('sampleproduct/order/history');
    }

}
