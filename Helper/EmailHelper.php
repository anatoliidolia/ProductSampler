<?php
declare(strict_types=1);

namespace PeachCode\SampleProduct\Helper;

use DateTime;
use DateTimeInterface;
use DOMDocument;
use Exception;
use Magento\Framework\Exception\NoSuchEntityException;
use PeachCode\SampleProduct\Model\Config\Config;
use PeachCode\SampleProduct\Model\Order;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Store\Model\StoreManagerInterface;
use PeachCode\SampleProduct\Model\Api\ConfigInterface;

class EmailHelper extends AbstractHelper
{
    /**
     * @var TransportBuilder
     */
    private TransportBuilder $_transportBuilder;

    /**
     * @var StoreManagerInterface
     */
    private StoreManagerInterface $_storeManager;

    /**
     * @var TimezoneInterface
     */
    private TimezoneInterface $localeDate;

    /**
     * @param Context               $context
     * @param TransportBuilder      $transportBuilder
     * @param StoreManagerInterface $storeManager
     * @param TimezoneInterface     $localeDate
     * @param Config                $config
     */
    public function __construct(
        Context                                              $context,
        TransportBuilder                                     $transportBuilder,
        StoreManagerInterface                                $storeManager,
        TimezoneInterface $localeDate,
        private readonly Config $config,
    ) {
        parent::__construct($context);
        $this->_transportBuilder = $transportBuilder;
        $this->_storeManager = $storeManager;
        $this->localeDate = $localeDate;
    }

    /**
     * @param $sampleOrder Order
     *
     * @return boolean
     * @throws NoSuchEntityException
     */
    public function sendEmail(Order $sampleOrder): bool
    {
        try {

            $destinationEmail = $this->config->getSampleOrdersEmail();

            if($destinationEmail == "") return false;

            $templateVariables = $this->prepareVariables($sampleOrder);

            $customerEmail = $sampleOrder->getCustomerEmail();

            $_SESSION[ConfigInterface::XML_SAMPLE_ORDER_ID] = $sampleOrder->getId();

            $store = $this->_storeManager->getStore()->getId();
            $transport = $this->_transportBuilder->setTemplateIdentifier('sampleproduct_order')
                ->setTemplateOptions(['area' => 'frontend', 'store' => $store])
                ->setTemplateVars($templateVariables)
                ->setFrom('general')
                ->addTo($customerEmail)
                ->addBcc($destinationEmail)
                ->getTransport();
            unset($_SESSION[ConfigInterface::XML_SAMPLE_ORDER_ID]);

            $transport->sendMessage();
        } catch (Exception $e) {
            $this->sendOrderErrorEmail($e->getMessage(),$e->getTraceAsString(),$sampleOrder);
            return false;
        }

        return true;

    }

    /**
     * @param $errorMessage
     * @param $stackTrace
     * @param $sampleOrder
     *
     * @return void
     * @throws NoSuchEntityException
     */
    private function sendOrderErrorEmail($errorMessage,$stackTrace,$sampleOrder): void
    {
        $errorEmail = $this->config->getSampleOrdersEmail();

        if($errorEmail == "") return;

        $store = $this->_storeManager->getStore()->getId();
        $transport = $this->_transportBuilder->setTemplateIdentifier('sampleproduct_order_error')
            ->setTemplateOptions(['area' => 'frontend', 'store' => $store])
            ->setTemplateVars([
                "errorMessage" => $errorMessage,
                "stackTrace"=>$stackTrace,
                'order_id'=> $sampleOrder->getId()
            ])
            ->setFrom('general')
            ->addTo($errorEmail, 'SampleProduct Error Receiver')
            ->getTransport();

        $transport->sendMessage();
    }


    /**
     * @param $sampleOrder Order
     *
     * @return array
     * @throws NoSuchEntityException
     */
    private function prepareVariables(Order $sampleOrder): array
    {
        return [
            'store' => $this->_storeManager->getStore(),
            'order_id'=> $sampleOrder->getId(),
            'customer_email' => $sampleOrder->getCustomerEmail(),
            'customer_account_number' => $sampleOrder->getCustomerAccountNumber(),
            'total_items' => $sampleOrder->getTotalItems(),
            'created_at' => $this->formatDate($sampleOrder->getCreatedAt()),
            'address_html' => $this->removeTelephoneFromAddress($sampleOrder->getHtmlAddress()),
            'order' => $sampleOrder,
        ];

    }

    /**
     * Retrieve formatting date
     *
     * @param null|string|DateTime $date
     * @param int                  $format
     * @param bool                 $showTime
     * @param null|string          $timezone
     *
     * @return string
     * @throws Exception
     */
    public function formatDate(
        $date = null,
        $format = \IntlDateFormatter::SHORT,
        $showTime = false,
        $timezone = null
    ): string {
        $date =  new DateTime($date ?? '');
        return $this->localeDate->formatDateTime(
            $date,
            $format,
            $showTime ? $format : \IntlDateFormatter::NONE,
            null,
            $timezone
        );
    }

    /**
     * @param $htmlAddress
     *
     * @return array|false|string|string[]
     */
    private function removeTelephoneFromAddress($htmlAddress): array|bool|string
    {

        $doc = new DOMDocument();
        $doc->loadHTML($htmlAddress);

        $telephoneElement = $doc->getElementById('telephone');

        $telephoneElement?->parentNode->removeChild($telephoneElement);

        $html = $doc->saveHTML();

        return str_replace("<br><br>","<br>",$html);
    }
}
