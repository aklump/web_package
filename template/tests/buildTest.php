<?php
/**
 * @file
 * PHPUnit tests for the build function
 *
 */
require_once dirname(__FILE__) . '/../vendor/autoload.php';

class buildTest extends \PHPUnit_Framework_TestCase {
  public function testReplaceHomepage() {
    $subject = ' * http://www.intheloftstudios.com/packages/jquery/jquery.click_replace';
    $control = ' * http://www.intheloftstudios.com/packages/jquery/jquery.click_replace_plus';
    js_replace_homepage($subject, 'http://www.intheloftstudios.com/packages/jquery/jquery.click_replace_plus');
    $this->assertSame($control, $subject);
  }  
  public function testReplaceDate() {
    $subject = ' * Date: Sat, 23 Nov 2013 09:23:06 -0800';
    $control = ' * Date: Sat, 29 May 2014 06:31:21 -0800';
    js_replace_date($subject, 'Sat, 29 May 2014 06:31:21 -0800');
    $this->assertSame($control, $subject);
  }
  public function testReplaceDescripton() {
    $subject = ' * some cool description';
    $control = ' * my new description';
    js_replace_description($subject, 'my new description');
    $this->assertSame($control, $subject);
  }
  public function testReplaceNameVersion() {
    $subject = ' * Click Replace jQuery JavaScript Plugin v0.1-rc8';
    $control = ' * Click Replace Plus jQuery JavaScript Plugin v1.2.4';
    js_replace_name_version($subject, 'Click Replace Plus', '1.2.4');
    $this->assertSame($control, $subject);
  }  
}

/** @} */ //end of group: name