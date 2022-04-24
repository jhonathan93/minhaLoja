<?php
/**
 * @author Jhonathan da silva
 * @link https://github.com/jhonathan93
 * @link https://www.linkedin.com/in/jhonathan-silva-367541171/
 * @package Jhonathan_ViaCep
 */

namespace Jhonathan\ViaCep\Logger;

use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Class Logger
 * @package Jhonathan\ViaCep\Logger
 */
class Logger extends \Monolog\Logger {

    /**
     * @var string
     */
    const XML_PATH_LOG_ENABLED = 'jhonathan_viacep/logging/enabled';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param string $name
     * @param array $handlers
     * @param array $processors
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct($name, ScopeConfigInterface $scopeConfig, array $handlers = array(), array $processors = array()) {
        parent::__construct($name, $handlers, $processors);
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @return bool
     */
    private function isLoggingEnabled(): bool {
        return (bool) $this->scopeConfig->getValue(self::XML_PATH_LOG_ENABLED);
    }

    /**
     * @param $level
     * @param $message
     * @param array $context
     * @return bool|null
     */
    public function log($level, $message, array $context = array()): ?bool {
//        if (!$this->isLoggingEnabled()) {
//            return null;
//        }
        return parent::log($level, $message, $context);
    }
}