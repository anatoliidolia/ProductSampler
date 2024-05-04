<?php
declare(strict_types=1);

namespace PeachCode\SampleProduct\Controller\Cart;

use Magento\Customer\Model\Session;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Message\ManagerInterface;
use PeachCode\SampleProduct\Helper\EmailHelper;
use PeachCode\SampleProduct\Helper\GeneralHelper;
use PeachCode\SampleProduct\Model\Cart;
use PeachCode\SampleProduct\Model\Order;
use PeachCode\SampleProduct\Model\OrderFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\Controller\ResultFactory;
use PeachCode\SampleProduct\Model\ResourceModel\Cart\CollectionFactory;
use PeachCode\SampleProduct\Model\Product\StockValidator;

class Submit implements ActionInterface
{
    /**
     * @var array|string[]
     */
    private array $requiredAddressFields = [
            "name",
            "company",
            "street",
            "city",
            "postcode",
        ];

    /**
     * @var array|string[]
     */
    private array $allAddressFields = [
            "name",
            "company",
            "telephone",
            "street",
            "city",
            "region",
            "postcode",
        ];

    public function __construct(
        private readonly RequestInterface $request,
        private readonly ResultFactory $resultFactory,
        private readonly ManagerInterface $messageManager,
        private readonly RedirectInterface $redirect,
        private readonly StockValidator $stockValidator,
        private readonly EmailHelper $emailHelper,
        private readonly OrderFactory $sampleOrderFactory,
        private readonly CollectionFactory $sampleCartCollectionFactory,
        private readonly Session $customerSession
    ) {
    }

    /**
     * @return ResultInterface|ResponseInterface|Redirect
     * @throws LocalizedException
     * @throws NotFoundException
     */
    public function execute(): ResultInterface|ResponseInterface|Redirect
    {

        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        if (!$this->request->isPost()) {
            throw new NotFoundException(__('Not Found.'));
        }

        if (!$this->customerSession->isLoggedIn()
            || !($customerId
                = $this->customerSession->getCustomerId())
        ) {
            throw new LocalizedException(__('User not logged in.'));
        }

        $addressValue = $this->validateAndStringifyAddress($this->request->getPost());

       if(!$addressValue){
           $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

           $this->messageManager->addErrorMessage(__("Please fill all fields"));

           return $resultRedirect->setUrl($this->redirect->getRefererUrl());
       }

        $cart = $this->getCurrentCart($customerId);

        /** @var $cart Cart */
        if (!$cart->getId()) {
            throw new LocalizedException(__('Cart not found.'));
        }

        $interceptionFlag = false;
        foreach ($cart->getAllItems() as $item) {
            if (!$this->stockValidator->checkIfSampleIsAvailable($item->getProductId())) {
                $interceptionFlag = true;
                $item->delete();
            }
        }

        if ($interceptionFlag) {
            throw new LocalizedException(__('Some samples are no longer available. They were removed from your cart.'));
        }


        $order = $this->createOrderFromCart($cart);
        $orderId = $order->getId();

        $order->setHtmlAddress($addressValue);
        $order->save();


        $emailSent = $this->emailHelper->sendEmail($order);

        $order->setEmailSent($emailSent);
        $order->save();

        //Delete cart after order is submitted
        $cart->delete();

        $this->messageManager->addSuccessMessage(__("Your order was created (ID: $orderId). You can see your SampleProduct order history in your account."));


        $resultRedirect->setUrl($this->redirect->getRefererUrl());

        return $resultRedirect;

    }

    /**
     * @param $customerId
     *
     * @return DataObject
     */
    private function getCurrentCart($customerId): DataObject
    {
        $collection = $this->sampleCartCollectionFactory->create();

        $collection->addFieldToFilter('customer_id', ['eq' => $customerId]);

        return $collection->getFirstItem();
    }

    /**
     * @throws LocalizedException
     */
    private function createOrderFromCart($cart): Order
    {
        $order = $this->sampleOrderFactory->create();

        $order->createFromCart($cart);

        return $order;
    }

    /**
     * @throws LocalizedException
     */
    private function validateAndStringifyAddress($post)
    {

        if (empty($post)) {
            throw new LocalizedException(__('No address data found.'));
        }

        $errors = [];

        $addressData = array_fill_keys($this->allAddressFields, "");

        //check if all fields are filled
        foreach ($this->allAddressFields as $addressField) {

            if (!isset($post[$addressField]) || $post[$addressField] == ""
                || !$this->checkIfArrayFieldIsSetOrStringIsWhitespace($post[$addressField])
            ) {

                if (in_array($addressField, $this->requiredAddressFields)) {
                    $errors[] = $addressField;
                }

            } else {
                $postedValue = $post[$addressField];

                if (is_array($postedValue)) {
                    for ($i = 0; $i < sizeof($postedValue); $i++) {
                        $postedValue[$i] = htmlspecialchars($postedValue[$i]);
                    }

                    $addressData[$addressField] = $postedValue;
                } else {
                    $addressData[$addressField] = htmlspecialchars($postedValue);

                }
            }

        }

        if (sizeof($errors) > 0) {
            return false;
        }

        $addressValue = '<p>';


        foreach ($addressData as $addrField => $addressDatum) {

            if (is_array($addressDatum)) {
                $addressDatum = $this->removeEmptyRows($addressDatum);
                $rowValue = implode("<br/>", $addressDatum);
            } else {
                $rowValue = $addressDatum;
            }

            if ($addrField == "telephone") {
                $rowValue = '<span id="telephone">'.$rowValue.'</span>';
            }

            if ($rowValue != "") {
                $addressValue .= $rowValue.'<br/>';
            }

        }

        $addressValue .= '</p>';

        return $addressValue;

    }

    /**
     * @param $value
     *
     * @return bool
     */
    private function checkIfArrayFieldIsSetOrStringIsWhitespace($value): bool
    {
        if (!is_array($value)) {
            if (trim($value) != "") {
                return true;
            }
        }

        foreach ($value as $valuePart) {
            if (trim($valuePart) != "") {
                return true;
            }
        }

        return false;
    }

    private function removeEmptyRows($rows): array
    {
        $newRows = [];

        foreach ($rows as $addressRow) {
            if (trim($addressRow) != "") {
                $newRows[] = $addressRow;
            }
        }

        return $newRows;
    }
}
