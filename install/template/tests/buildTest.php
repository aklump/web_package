<?php
/**
 * @file
 * PHPUnit tests for the build function
 *
 */
require_once dirname(__FILE__) . '/../vendor/autoload.php';

class buildTest extends \PHPUnit_Framework_TestCase {
  

  /**
   * Provides data for testReplaceJsFunction.
   *
   * @return 
   *   - 0: 
   */
  function replaceJsFunctionTestProvider() {
    return array(
      array('    this.version = "1.0.0";', '2.9.3', '    this.version = "2.9.3";'),
      array("$.fn.areaMapper.version = function() { return '0.1.2'; };", "1.6", "$.fn.areaMapper.version = function() { return '1.6'; };"),
    );
  }
  
  /**
   * @dataProvider replaceJsFunctionTestProvider 
   */
  function testReplaceJsFunction($source, $new_version, $control) {
    js_replace_version_function($source, $new_version, $control);
    $this->assertSame($control, $source);
  }

  /**
   * Provides data for testReplaceCopyright.
   *
   * @return 
   *   - 0: 
   */
  function testReplaceCopyrightProvider() {
    return array(
      array(
        " * Copyright 2013-2014, Paul Bunyan",
        "2015",
        "Aaron Klump",
        " * Copyright 2013-2015, Aaron Klump",
      ),
      array(
        " * Copyright 2013, {{ name }}",
        "2015",
        "Aaron Klump",
        " * Copyright 2013-2015, Aaron Klump",
      ),
      array(
        " * Copyright 2013, {{ name }}",
        "2013",
        "In the Loft Studios",
        " * Copyright 2013, In the Loft Studios",
      ),
    );
  }
  
  /**
   * @dataProvider testReplaceCopyrightProvider 
   */
  public function testReplaceCopyright($source, $year, $name, $control) {
    js_replace_copyright($source, $name, $year);
    $this->assertSame($control, $source);
  }

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
  /**
   * Provides data for testNameVersion.
   *
   * @return 
   *   - 0: 
   */
  function testNameVersionProvider() {
    return array(
      array(
        ' * LoftImages JS Module v1.0.9',
        'Apple Blossom',
        '0.1',
        ' * Apple Blossom JS Module v0.1',
      ),
      array(
        ' * {{ name }} Javascript Module v{{ version }}',
        'Apple Blossom',
        '0.1',
        ' * Apple Blossom Javascript Module v0.1',
      ),
      array(
        ' * {{ name }} jQuery JavaScript Plugin v{{ version }}',
        'Apple Blossom',
        '0.1',
        ' * Apple Blossom jQuery JavaScript Plugin v0.1',
      ),
      array(
        ' * Click Replace jQuery JavaScript Plugin v0.1-rc8',
        'Click Replace Plus',
        '1.2.4',
        ' * Click Replace Plus jQuery JavaScript Plugin v1.2.4',
      ),
    );
  }
  
  /**
   * @dataProvider testNameVersionProvider 
   */
  public function testReplaceNameVersion($subject, $name, $version, $control) {
    js_replace_name_version($subject, $name, $version);
    $this->assertSame($control, $subject);
  }  
}

/** @} */ //end of group: name