<?php

/**
 * Netresearch_OPS_Helper_Data
 *
 * @package
 * @copyright 2014 Netresearch
 * @author    Sebastian Ertner <sebastian.ertner@netresearch.de>
 * @license   OSL 3.0
 */
class Netresearch_OPS_Helper_Version extends Mage_Core_Helper_Abstract
{
    const CAN_USE_APPLICABLE_FOR_QUOTE_EE_MINOR = 14;

    const CAN_USE_APPLICABLE_FOR_QUOTE_CE_MINOR = 8;


    /**
     * checks if Version is above EE 1.13 or CE 1.7.0.2
     * useApplicableForQuote was implemented in CE 1.8 / EE 1.14
     *
     * @param $edition
     *
     * @return bool
     */
    public function canUseApplicableForQuote($edition)
    {
        $result      = false;
        $versionInfo = $this->getVersionInfo();
        if (array_key_exists('minor', $versionInfo)
            && $versionInfo['minor'] >= $this->getVersionForEdition($edition)
        ) {
            $result = true;
        }

        return $result;
    }

    /**
     * return allowed version
     *
     * @param $edition
     *
     * @return int
     */
    protected function getVersionForEdition($edition)
    {
        if (Mage::EDITION_ENTERPRISE == $edition) {
            return self::CAN_USE_APPLICABLE_FOR_QUOTE_EE_MINOR;
        }

        return self::CAN_USE_APPLICABLE_FOR_QUOTE_CE_MINOR;
    }

    /**
     * wraps Mage::getVersionInfo since Mage class is final and not testable
     *
     * @return array
     */
    protected function getVersionInfo()
    {
        return Mage::getVersionInfo();
    }


}
