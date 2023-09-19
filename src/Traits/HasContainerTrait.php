<?php

namespace AKlump\WebPackage\Traits;


use Psr\Container\ContainerInterface;

trait HasContainerTrait {

  /**
   * @var \Psr\Container\ContainerInterface
   */
  private $container;

  public function containerGet(string $dependency_name) {
    return $this->container->get($dependency_name);
  }

  public function setContainer(ContainerInterface $container) {
    $this->container = $container;
  }

}
