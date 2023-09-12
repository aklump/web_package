<?php

namespace AKlump\WebPackage\Helpers;

use AKlump\WebPackage\Hooks\HookEvent;

class GetHookEvent {

  use \AKlump\WebPackage\Traits\HasConfigTrait;

  public function __construct($config) {
    $this->setConfig($config);
  }

  /**
   * @return \AKlump\WebPackage\Hooks\HookEvent
   *   A hook event with known info already set.
   */
  public function __invoke(): HookEvent {
    $event = new HookEvent();
    $info = (new GetInfo($this->getConfig()))();
    $event->setPackageName($info['name'] ?? '');
    $event->setDescription($info['description'] ?? '');
    $event->setAuthor($info['author'] ?? '');
    $event->setHomepage($info['homepage'] ?? '');

    return $event;
  }

}
