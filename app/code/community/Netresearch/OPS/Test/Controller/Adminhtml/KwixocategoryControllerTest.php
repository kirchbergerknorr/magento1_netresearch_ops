<?php
class Netresearch_OPS_Test_Controller_Adminhtml_KwixocategoryControllerTest
    extends EcomDev_PHPUnit_Test_Case_Controller
{
    public function setUp()
    {
        parent::setUp();
        $fakeUser = $this->getModelMock('admin/user', array('getId', 'getRole'));
        $fakeUser->expects($this->any())
                 ->method('getId')
                 ->will($this->returnValue(1));

        $sessionMock = $this->getModelMock(
            'admin/session', array('getUser', 'init', 'save', 'isAllowed')
        );
        $sessionMock->expects($this->any())
                    ->method('getUser')
                    ->will($this->returnValue($fakeUser));

        $sessionMock->expects($this->any())
                    ->method('isAllowed')
                    ->will($this->returnValue(true));

        $this->replaceByMock('singleton', 'admin/session', $sessionMock);
    }


    /**
     * @loadFixture category_mapping
     */
    public function testDeleteAction()
    {
        $this->dispatch('adminhtml/kwixocategory/delete', array());
        $model = Mage::getModel('ops/kwixo_category_mapping')->load(1);
        $this->assertEquals(1, $model->getId());
        $this->dispatch('adminhtml/kwixocategory/delete', array('id' => 666));
        $model = Mage::getModel('ops/kwixo_category_mapping')->load(1);
        $this->assertEquals(1, $model->getId());
        $this->dispatch('adminhtml/kwixocategory/delete', array('id' => 10));
        $model = Mage::getModel('ops/kwixo_category_mapping')->load(1);
        $this->assertNull($model->getId());
        Mage::getSingleton('adminhtml/session')->getMessages(true);
    }

}