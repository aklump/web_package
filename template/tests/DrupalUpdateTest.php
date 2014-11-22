<?php
/**
 * @file
 * PHPUnit tests for the DrupalUpdate class
 */
namespace AKlump\WebPackage;

require_once dirname(__FILE__) . '/../vendor/autoload.php';

class DrupalUpdateTest extends \PHPUnit_Framework_TestCase {
  
  public function testSave() {
    $obj = new DrupalUpdate(dirname(__FILE__) . '/tmp');
    $path = dirname(__FILE__) . '/tmp/update/file2.xml';
    $this->assertFileNotExists($path);
    $obj->save('file2.xml');
    $this->assertFileExists($path);
    unlink($path);
  }

  public function testAppendXmlAsExtension() {
    $obj = new DrupalUpdate(dirname(__FILE__) . '/tmp');
    $path = dirname(__FILE__) . '/tmp/update/file.xml';
    $this->assertFileNotExists($path);
    $obj->save('file');
    $this->assertFileExists($path);
    unlink($path);
  }

  /**
   * @expectedException Exception
   */
  public function testSetXmlBadObjectException () {
    $obj = new DrupalUpdate(dirname(__FILE__) . '/bogus');
    $obj->setUp()->xml(new \stdClass);
  }

  /**
   * @expectedException Exception
   */
  public function testSetXmlBadStringException () {
    $obj = new DrupalUpdate(dirname(__FILE__) . '/bogus');
    $obj->setUp()->xml('bad string');
  }

  public function testXmlSet() {
    $obj = new DrupalUpdate(dirname(__FILE__) . '/tmp');
    $return = $obj->xml('<project><title>Web Package</title></project>');
    $this->assertInstanceOf('SimpleXMLElement', $return);
    $this->assertSame('Web Package', (string) $return->title);
  }

  public function testXmlGet() {
    $obj = new DrupalUpdate(dirname(__FILE__) . '/tmp');
    $this->assertInstanceOf('SimpleXMLElement', $obj->xml());
  }

  public function testSetup () {
    $obj = new DrupalUpdate(dirname(__FILE__) . '/tmp');
    $return = $obj->setUp();
    $this->assertInstanceOf('AKlump\WebPackage\DrupalUpdate', $return);
    $this->assertTrue(is_dir(dirname(__FILE__) . '/tmp/update'));
    rmdir(dirname(__FILE__) . '/tmp/update');
  }

  /**
   * @expectedException Exception
   */
  public function testSetupException () {
    $obj = new DrupalUpdate(dirname(__FILE__) . '/bogus');
    $return = $obj->setUp();  
  }
}
