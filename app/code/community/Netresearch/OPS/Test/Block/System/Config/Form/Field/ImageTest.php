<?php
/**
 * Netresearch OPS
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to
 * newer versions in the future.
 *
 * PHP version 5
 *
 * @category  OPS
 * @package   Netresearch_OPS
 * @author    Benjamin Heuer <benjamin.heuer@netresearch.de>
 * @copyright 2016 Netresearch GmbH & Co. KG
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.netresearch.de/
 */

/**
 * Netresearch_OPS_Test_Block_System_Config_ImageTest
 *
 * @category OPS
 * @package  Netresearch_OPS
 * @author   Benjamin Heuer <benjamin.heuer@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 */
class Netresearch_OPS_Test_Block_System_Config_Form_Field_ImageTest
    extends EcomDev_PHPUnit_Test_Case
{
    /**
     * @test
     */
    public function getElementHtml()
    {
        $blockMock =
            $this->getBlockMock('ops/system_config_form_field_image', array('getHtmlId', '_getDeleteCheckbox'), false,
                array(), '', false
            );

        $blockMock->expects($this->any())
                  ->method('getHtmlId')
                  ->will($this->returnValue('112'));
        $blockMock->expects($this->any())
                  ->method('_getDeleteCheckbox')
                  ->will($this->returnValue(''));

        /** @var Netresearch_OPS_Block_System_Config_Form_Field_Image $imageBlock */
        $blockMock->setData('name', 'groups[ops_cc][fields][image][value]');

        $result = $blockMock->getElementHtml();

        $this->assertInternalType('string', $result);
        $this->assertContains('ops/logos/ops_cc.png', $result);

        /* Fill the Block with an Url */
        $simpleXmlElement = new Varien_Simplexml_Element('<xml><base_url>ingenico/test</base_url></xml>');
        $blockMock->setData('value', 'default/img.png');
        $blockMock->setData('field_config', $simpleXmlElement);
        $result = $blockMock->getElementHtml();
        $this->assertInternalType('string', $result);
        $this->assertContains('ingenico/test', $result);
    }

}
