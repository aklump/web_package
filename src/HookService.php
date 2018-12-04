<?php

namespace AKlump\WebPackage;

use AKlump\LoftLib\Storage\FilePath;

class HookService {

  protected $pathToInstance;

  protected $pathToDist;

  protected $sourceFile;

  protected $infoFile;

  protected $messages = [];

  public function __construct(
    FilePath $info_file,
    $instance_root,
    $name,
    $description,
    $version,
    $previous_version,
    $author,
    $url,
    $date_string
  ) {
    $this->pathToInstance = rtrim($instance_root, '/');
    $this->infoFile = $info_file;
    $this->data = [
      'name' => $name,
      'description' => $description,
      'version' => $version,
      'previous_version' => $previous_version,
      'author' => $author,
      'url' => $url,
      'date_string' => $date_string,
    ];
  }

  public function load($filepath) {
    $this->sourceFile = FilePath::create($this->resolve($filepath));
    if (!$this->sourceFile->exists()) {
      throw new BuildFailException("$filepath does not exist.");
    }
    $this->sourceFile->load();

    return $this;
  }

  public function __get($key) {
    return $this->data[$key] ?? NULL;
  }

  public function replace(array $additional_token_map = []) {
    $token_map = $additional_token_map;
    $token_map += array(
      '__version' => $this->version,
      '__name' => $this->name,
      '__description' => wordwrap($this->description, 75),
      '__date' => $this->date_string,
      '__author' => $this->author,
      '__homepage' => $this->url,
    );

    $info = $this->infoFile->load()->getJson(TRUE);
    if (isset($info['title'])) {
      $token_map['__title'] = $info['title'];
    }
    $code = $this->sourceFile->get();
    $code = str_replace(array_keys($token_map), array_values($token_map), $code);

    // Replace the year which will appear as '__year' or '2015__year'.
    $code = preg_replace_callback('/(\s\d{2,4}?)__year/', function ($matches) {
      $years = array();
      isset($matches[1]) && $years[] = $matches[1];
      $years[] = date('Y');

      return implode('-', $years);
    }, $code);

    $this->sourceFile->put($code);
    $this->messages[] = 'Tokens have been replaced.';

    return $this;
  }

  public function setDistributionDir(string $dist) {
    $dist = $this->resolve($dist);
    if (!file_exists($dist)) {
      throw new BuildFailException("Failed setting dist folder to \"$dist\"; it does not exist.");
    }
    $this->pathToDist = $dist;
  }

  public function saveToDist() {
    if (is_null($this->pathToDist)) {
      throw new BuildFailException("You must use setDistributionDir() to specifiy a distribution directory.");
    }

    return $this->saveTo($this->pathToDist);

  }

  public function saveTo(string $dir = 'dist') {
    $to_dir = FilePath::create($this->resolve($dir));
    if (!$to_dir->exists()) {
      throw new BuildFailException("Cannot save to \"{$to_dir->getPath()}\"; it does not exist.");
    }
    $to = $to_dir->to($this->sourceFile->getBasename())
      ->put($this->sourceFile->get());
    if (($source = $this->sourceFile->getPath()) === $to->getPath()) {
      throw new BuildFailException("You have asked to save over your source file, which cannot be done: \"$source\".");
    }
    $to->save();
    $this->messages[] = $to->getPath() . ' has been saved.';
    $this->sourceFile = NULL;

    return $this;
  }

  protected function resolve($filepath) {
    if (substr($filepath, 0, 1) !== '/') {
      $filepath = $this->pathToInstance . '/' . $filepath;
    }

    return $filepath;
  }

  public function displayMessages() {
    echo implode(PHP_EOL, $this->messages);
  }
}
