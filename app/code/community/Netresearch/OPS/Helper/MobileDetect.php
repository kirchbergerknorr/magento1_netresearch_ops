<?php
/**
 * Netresearch_OPS_Helper_MobileDetect
 *
 * @package
 * @copyright 2016 Netresearch
 * @author    Sebastian Ertner <sebastian.ertner@netresearch.de>
 * @license   OSL 3.0
 */
require_once Mage::getBaseDir('lib') . DS . 'MobileDetect' .DS. 'Mobile_Detect.php';

class Netresearch_OPS_Helper_MobileDetect extends Mage_Core_Helper_Abstract
{
    /**
     * Computer device type string
     */
    const DEVICE_TYPE_COMPUTER = 'Computer';
    /**
     * mobile device type string
     */
    const DEVICE_TYPE_MOBILE   = 'Mobile';
    /**
     * tablet device type string
     */
    const DEVICE_TYPE_TABLET   = 'Tablet';

    /**
     * @var Mobile_Detect
     */
    private $_detector = null;

    /**
     * create class instance
     *
     * Netresearch_OPS_Helper_MobileDetect constructor.
     * @param null $headers
     * @param null $userAgent
     * @param null $detector
     */
    public function __construct()
    {
            $this->_detector = new Mobile_Detect();
    }

    public function setDetector($detector)
    {
        $this->_detector = $detector;
    }

    /**
     * determine device type with help of mobile_detect lib and return it
     *
     * @return string
     */
    public function getDeviceType()
    {
        $deviceType = self::DEVICE_TYPE_COMPUTER;
        if ($this->_detector->isMobile()) {
            $deviceType = self::DEVICE_TYPE_MOBILE;
        }

        if ($this->_detector->isTablet()) {
            $deviceType = self::DEVICE_TYPE_TABLET;
        }

        return $deviceType;
    }
}
