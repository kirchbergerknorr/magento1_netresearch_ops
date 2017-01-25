<?php

class Netresearch_OPS_Block_System_Config_Support extends Mage_Adminhtml_Block_Abstract
    implements Varien_Data_Form_Element_Renderer_Interface
{
    protected $_template = 'ops/system/config/support.phtml';
    protected $_downloadLogPath = 'admin/downloadlog';

    protected function getConfig()
    {
        return Mage::getModel('ops/config');
    }

    /**
     * Render fieldset html
     *
     * @param Varien_Data_Form_Element_Abstract $fieldset
     * @return string
     */
    public function render(Varien_Data_Form_Element_Abstract $fieldset)
    {
        $originalData = $fieldset->getOriginalData();
        $this->addData(
            array(
                           'fieldset_label' => $fieldset->getLegend(),
            )
        );
        return $this->toHtml();
    }

    /**
     * get extension version
     *
     * @return string
     */
    public function getExtensionVersion()
    {
        return (string)Mage::getConfig()->getNode('modules')->children()->Netresearch_OPS->version;
    }

    /**
     * get Magento version
     *
     * @return string
     */
    public function getMageVersion()
    {
        $mageVersion = Mage::getVersion();
        if (is_callable('Mage::getEdition')) {
            $mageVersion = Mage::getEdition() . ' ' . $mageVersion;
        }
        return $mageVersion;
    }

    /**
     * get support mail address
     *
     * @return string
     */
    public function getSupportMail()
    {
        $mail = $this->getConfig()->getConfigData('support_mail');
        if (false !== strpos($mail, '@')) {
            return $mail;
        }
    }

    /**
     * if we have a link to documentation
     *
     * @return int
     */
    public function hasDocumentation()
    {
        return strlen($this->getDocLinkDe() . $this->getDocLinkEn());
    }

    /**
     * get URL of German documentation
     *
     * @return string
     */
    public function getDocLinkDe()
    {
        $link = $this->getConfig()->getConfigData('doc_link_de');
        if (false !== strpos($link, '://')) {
            return $link;
        }
    }

    /**
     * get URL of English documentation
     *
     * @return string
     */
    public function getDocLinkEn()
    {
        $link = $this->getConfig()->getConfigData('doc_link_en');
        if (false !== strpos($link, '://')) {
            return $link;
        }
    }

    /**
     * if we have link to a FAQ
     *
     * @return int
     */
    public function hasFaq()
    {
        return strlen($this->getFaqLinkDe() . $this->getFaqLinkEn());
    }
    /**
     * get URL of German FAQ
     *
     * @return string
     */
    public function getFaqLinkDe()
    {
        $link = $this->getConfig()->getConfigData('faq_link_de');
        if (false !== strpos($link, '://')) {
            return $link;
        }

    }

    /**
     * get URL of English FAQ
     *
     * @return string
     */
    public function getFaqLinkEn()
    {
        $link = $this->getConfig()->getConfigData('faq_link_en');
        if (false !== strpos($link, '://')) {
            return $link;
        }
    }

    /**
     * if we use a prefix for parameter ORDERID
     *
     * @return bool
     */
    public function hasDevPrefix()
    {
        return 0 < strlen($this->getDevPrefix());
    }

    /**
     * get prefix for parameter ORDERID
     *
     * @return string
     */
    public function getDevPrefix()
    {
        return $this->getConfig()->getConfigData('devprefix');
    }

    /**
     * get link for ops.log download action
     *
     * @return string
     */
    public function getLogDownloadLink()
    {
        return $this->getUrl($this->_downloadLogPath);
    }
}

