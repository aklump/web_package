<?php

namespace AKlump\WebPackage\Helpers;

use AKlump\WebPackage\Traits\HasConfigTrait;

class ShouldCreateTag {

  const CONFIG_KEY = 'create_tags';

  use HasConfigTrait;

  public function __construct(array $config) {
    $this->setConfig($config);
  }

  public function __invoke(string $old_version, string $new_version): bool {
    $config_value = $this->getConfig()[self::CONFIG_KEY] ?? NULL;
    $config_value = $this->normalize($config_value) ?? VersionDegree::PATCH;
    if (is_bool($config_value)) {
      return $config_value;
    }

    $degree = (new GetVersionChangeDegree())($old_version, $new_version);

    if ($config_value === VersionDegree::PATCH && in_array($degree, [
        VersionDegree::PATCH,
        VersionDegree::MINOR,
        VersionDegree::MAJOR,
      ])) {
      return TRUE;
    }
    elseif ($config_value === VersionDegree::MINOR && in_array($degree, [
        VersionDegree::MINOR,
        VersionDegree::MAJOR,
      ])) {
      return TRUE;
    }
    elseif ($config_value === VersionDegree::MAJOR && in_array($degree, [
        VersionDegree::MAJOR,
      ])) {
      return TRUE;
    }

    return FALSE;
  }

  private function normalize($value) {
    if (is_string($value)) {
      $value = strtolower($value);
    }
    if ('no' === $value) {
      return FALSE;
    }
    elseif ('yes' === $value) {
      return TRUE;
    }
    elseif (in_array($value, [
      VersionDegree::MAJOR,
      VersionDegree::MINOR,
      VersionDegree::PATCH,
    ])) {
      return $value;
    }

    return NULL;
  }

}
