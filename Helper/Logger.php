<?php
declare(strict_types=1);

namespace PeachCode\SampleProduct\Helper;

use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Logger\Handler\BaseFactory;
use Magento\Framework\Logger\MonologFactory;
use Psr\Log\LoggerInterface;

class Logger
{
    public const PeachCode_EXCEPTION = 'exception';
    public const PeachCode_INFO = 'info';

    /**
     * @var LoggerInterface[]
     */
    private array $loggers = [];

    /**
     * @var BaseFactory
     */
    private BaseFactory $loggerHandlerFactory;

    /**
     * @var MonologFactory
     */
    private MonologFactory $loggerFactory;

    /**
     * @var File
     */
    private File $file;

    /**
     * @param BaseFactory $loggerHandlerFactory
     * @param MonologFactory $loggerFactory
     * @param File $file
     */
    public function __construct(BaseFactory $loggerHandlerFactory, MonologFactory $loggerFactory, File $file)
    {
        $this->loggerHandlerFactory = $loggerHandlerFactory;
        $this->loggerFactory = $loggerFactory;
        $this->file = $file;
    }

    /**
     * @param $msg
     * @param $type
     *
     * @return void
     */
    public function log($msg, $type = self::PeachCode_INFO): void
    {
        $this->getLogger($type)->info($msg);
    }

    /**
     * @param string $type
     *
     * @return LoggerInterface
     */
    private function getLogger(string $type): LoggerInterface
    {
        if (isset($this->loggers[$type])) {
            return $this->loggers[$type];
        }

        return $this
            ->loggerFactory
            ->create(
                [
                    'name' => $type,
                    'handlers' => [
                        'debug' => $this
                            ->loggerHandlerFactory
                            ->create([
                                'fileName' => '/var/log/sampleproduct_' . $type . '.log',
                                'filesystem' => $this->file
                            ])
                    ]
                ]
            );
    }
}
