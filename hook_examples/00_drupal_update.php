<?php
/**
 * @file
 * A build job that will create the xml files for alternate update servers.
 *
 * @ingroup web_package
 * @{
 */
require_once dirname(__FILE__) . '/../vendor/autoload.php';
use \AKlump\WebPackage\DrupalUpdate as Update;

$template = <<<EOD
<project xmlns:dc="http://purl.org/dc/elements/1.1/">
  <title/>
  <short_name>loft_deploy</short_name>
  <dc:creator>aklump</dc:creator>
  <type>project_module</type>
  <api_version>7.x</api_version>
  <recommended_major>1</recommended_major>
  <supported_majors>1</supported_majors>
  <default_major>1</default_major>
  <project_status>published</project_status>
  <link/>
  <terms>
    <term>
      <name>Projects</name>
      <value>Modules</value>
    </term>
    <term>
      <name>Maintenance status</name>
      <value>Actively maintained</value>
    </term>
    <term>
      <name>Development status</name>
      <value>Under active development</value>
    </term>
    <term>
      <name>Module categories</name>
      <value>Development</value>
    </term>
  </terms>
  <releases/>
</project>
EOD;

$obj = new Update($argv[7]);

try {
  $obj
    ->setup()
    ->xml($template);  

  $obj->xml()->title = $argv[3];
  
  // homepage link
  if ($argv[5]) {
  $obj->xml()->link = $argv[5];
  }

  // author
  if ($argv[6]) {
    $ns = $obj->xml()->getNameSpaces(true);
    $ns = $obj->xml()->children($ns['dc']);
    $ns->creator = $argv[6];
  }

  $obj->save('7.x');

} catch (Exception $e) {
  die($e->getMessage());
}
