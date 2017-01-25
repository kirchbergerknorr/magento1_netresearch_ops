<?php

class Netresearch_OPS_Test_Model_File_Download extends EcomDev_PHPUnit_Test_Case
{
    protected $testFile;
    public function setUp()
    {
        $this->testFile = Mage::helper('ops/data')->getLogPath();
        if(!file_exists($this->testFile)){
            $file = fopen($this->testFile, 'c');
            fclose($file);
        }

    }
    /**
     * @expectedException Exception
     */
    public function testFailingGetFile()
    {
        $model = Mage::getModel('ops/file_download');
        $path = 'abc';
        $model->getFile($path);
    }

    public function testSuccessGetFile()
    {
        $model = Mage::getModel('ops/file_download');
        if(filesize($this->testFile) > $model::ONE_MEGABYTE){
            $this->assertEquals(0, strpos(basename($model->getFile($this->testFile)), 'tempFile'));
        }else{
            $this->assertEquals($model->getFile($this->testFile), $this->testFile);
        }

    }

}
 