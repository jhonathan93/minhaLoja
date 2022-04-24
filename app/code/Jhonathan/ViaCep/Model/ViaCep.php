<?php
/**
 * @author Jhonathan da silva
 * @link https://github.com/jhonathan93
 * @link https://www.linkedin.com/in/jhonathan-silva-367541171/
 * @package Jhonathan_ViaCep
 */

namespace Jhonathan\ViaCep\Model;

use Magento\Framework\Serialize\Serializer\Json;
use Jhonathan\ViaCep\Api\ViaCepInterface;
use Jhonathan\ViaCep\Helper\Data;

use Exception;

/**
 * Class ViaCep
 * @package Jhonathan\Customer\Model\Frontend
 */
class ViaCep implements ViaCepInterface {

    /**
     * @var Data
     */
    private $helper;

    /**
     * @var Json
     */
    private $json;

    /**
     * @param Data $helper
     * @param Json $json
     */
    public function __construct(Data $helper, Json $json) {
        $this->helper = $helper;
        $this->json = $json;
    }

    /**
     * @param string $zipcode
     * @return string
     */
    public function searchAddressByCep(string $zipcode): string {
        try {
            return $this->helper->request(preg_replace("/[^0-9]/", "", $zipcode ));
        } catch (Exception $e) {
            return 'error';
        }
    }
}