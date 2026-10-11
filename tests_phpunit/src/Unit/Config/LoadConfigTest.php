<?php

namespace AKlump\WebPackage\Tests\Unit\Config;

use AKlump\WebPackage\Config\Config;
use AKlump\WebPackage\Config\ConfigDefaults;
use AKlump\WebPackage\Config\LoadConfig;
use AKlump\WebPackage\Tests\TestingTraits\TempProjectTrait;
use PHPUnit\Framework\TestCase;

/**
 * @covers \AKlump\WebPackage\Config\LoadConfig
 * @uses \AKlump\WebPackage\Config\ConfigManager
 * @uses \AKlump\WebPackage\Config\Loader\LegacyLoader
 * @uses \AKlump\WebPackage\Config\Loader\YamlLoader
 * @uses \AKlump\WebPackage\Helpers\GetServerHome
 */
class LoadConfigTest extends TestCase {

  use TempProjectTrait;

  protected function tearDown(): void {
    $this->removeTempProject();
  }

  public function testDefaultsAreReturnedWhenThereIsNoConfiguration() {
    $root = $this->createTempProject();
    $config = (new LoadConfig($root))();
    $this->assertSame(ConfigDefaults::MAIN_BRANCH, $config[Config::MAIN_BRANCH]);
    $this->assertSame(ConfigDefaults::DEVELOP_BRANCH, $config[Config::DEVELOP_BRANCH]);
  }

  public function testProjectConfigOverridesHomeConfig() {
    $root = $this->createTempProject([
      Config::MAIN_BRANCH => 'main',
      'name' => 'Lorem',
    ], [], [
      '.web_package/config.yml' => "master: trunk\nname: Home\nauthor: Home Author\n",
    ]);
    $config = (new LoadConfig($root))();
    $this->assertSame('main', $config[Config::MAIN_BRANCH]);
    $this->assertSame('Lorem', $config['name']);
    $this->assertSame('Home Author', $config['author']);
  }

  public function testLocalConfigOverridesProjectConfig() {
    $root = $this->createTempProject([
      'name' => 'Lorem',
      'description' => 'Ipsum',
    ], [
      '.web_package/config.local.yml' => "name: Local\n",
    ]);
    $config = (new LoadConfig($root))();
    $this->assertSame('Local', $config['name']);
    $this->assertSame('Ipsum', $config['description']);
  }

  public function testRootPathDeterminesWhichProjectIsLoaded() {
    $root = $this->createTempProject(['name' => 'Lorem']);
    $this->assertSame('Lorem', (new LoadConfig($root))()['name']);
    $this->assertArrayNotHasKey('name', (new LoadConfig($root . '/does_not_exist'))());
  }

  public function testLegacyExtensionlessConfigIsLoaded() {
    $root = $this->createTempProject([], [
      '.web_package/config' => "# A comment\nname = Legacy\nmicro = 4\n",
    ]);
    $config = (new LoadConfig($root))();
    $this->assertSame('Legacy', $config['name']);
    $this->assertArrayNotHasKey('micro', $config);
    $this->assertSame('4', $config['patch']);
  }

  public function testTemplateArgumentLoadsTheHomeTemplate() {
    $root = $this->createTempProject(['name' => 'Lorem'], [], [
      '.web_package/config_foo.yml' => "author: Template Author\n",
      '.web_package/config.yml' => "author: Default Author\n",
    ]);
    $this->assertSame('Template Author', (new LoadConfig($root))('foo')['author']);
  }

  public function testTemplateInProjectConfigLoadsTheHomeTemplate() {
    $root = $this->createTempProject(['template' => 'foo'], [], [
      '.web_package/config_foo.yml' => "author: Template Author\n",
      '.web_package/config.yml' => "author: Default Author\n",
    ]);
    $this->assertSame('Template Author', (new LoadConfig($root))()['author']);
  }

  public function testDefaultVersionFileIsMadeAbsoluteAgainstTheRoot() {
    $root = $this->createTempProject(['name' => 'Lorem']);
    $config = (new LoadConfig($root))();
    $this->assertSame($root . '/' . ConfigDefaults::VERSION_FILE, $config[Config::VERSION_FILE]);
  }

  public function testRelativeVersionFileIsMadeAbsoluteAgainstTheRoot() {
    $root = $this->createTempProject([Config::VERSION_FILE => 'composer.json']);
    $config = (new LoadConfig($root))();
    $this->assertSame($root . '/composer.json', $config[Config::VERSION_FILE]);
  }

  public function testGitRootIsFoundInTheRoot() {
    $root = $this->createTempProject(['name' => 'Lorem'], ['.git/HEAD' => 'ref: refs/heads/main']);
    $this->assertSame('.', (new LoadConfig($root))()['git_root']);
  }

  public function testGitRootIsFoundInAnAncestor() {
    $root = $this->createTempProject(['name' => 'Lorem'], ['.git/HEAD' => 'ref: refs/heads/main']);
    mkdir($root . '/child/.web_package', 0755, TRUE);
    file_put_contents($root . '/child/.web_package/config.yml', "name: Child\n");
    $config = (new LoadConfig($root . '/child'))();
    $this->assertSame('Child', $config['name']);
    $this->assertSame('..', $config['git_root']);
  }

}
