<?php
/**
 * @author Jhonathan da silva
 * @link https://github.com/jhonathan93
 * @link https://www.linkedin.com/in/jhonathan-silva-367541171/
 * @package Jhonathan_ViaCep
 */

namespace Jhonathan\ViaCep\Logger;

use Magento\Framework\Filesystem\DriverInterface;
use Magento\Framework\Logger\Handler\Base;
use Monolog\Logger;

use Exception;

/**
 * Class Handler
 * @package Jhonathan\ViaCep\Logger
 */
class Handler extends Base {

    /**
     * @var string
     */
    protected $fileName = '/var/log/viacep.log';

    /**
     * @var int
     */
    protected $loggerType = Logger::ALERT;

    /**
     * @param DriverInterface $filesystem
     * @param string $filePath
     * @param string $fileName
     * @throws Exception
     */
    public function __construct(DriverInterface $filesystem, $filePath = null, $fileName = null) {
        parent::__construct($filesystem, $filePath, $fileName);
    }
}