<?php

namespace AKlump\WebPackage;

use AKlump\LoftLib\Bash\Bash;
use AKlump\LoftLib\Bash\Color;
use AKlump\LoftLib\Storage\FilePath;

/**
 * Provide common build functionality to PHP hooks.
 */
class HookService {

  protected $php = 'php';

  protected $pathToWebPackage;

  protected $pathToInstance;

  protected $pathToDist;

  protected $sourceFile;

  protected $infoFile;

  protected $sourceCode;

  protected $messages = [];

  protected $scmFilesToAdd = [];

  public function __construct(
    FilePath $path_to_web_package,
    FilePath $info_file,
    FilePath $instance_root,
    $name,
    $description,
    $version,
    $previous_version,
    $author,
    $url,
    $date_string
  ) {
    $this->pathToWebPackage = rtrim($path_to_web_package->getPath(), '/');
    $this->pathToInstance = rtrim($instance_root->getPath(), '/');
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

  public function loadFile($filepath) {
    $this->sourceFile = FilePath::create($this->resolve($filepath));
    if (!$this->sourceFile->exists()) {
      throw new BuildFailException("$filepath does not exist.");
    }
    $this->setSourceCode($this->sourceFile->load()->get());
    $this->startMessageClause($this->relativize($this->sourceFile->getPath()) . ' has been loaded.');

    return $this;
  }

  public function __get($key) {
    return $this->data[$key] ?? NULL;
  }

  /**
   * Replace tokens in $this->sourceCode.
   *s
   *
   * @param array $additional_token_map
   *
   * @return $this
   *
   * @see ::setSourceCode
   * @see ::getSourceCode
   */
  public function replaceTokens(array $additional_token_map = []) {
    $token_map = $additional_token_map;
    $token_map += array(
      '__author' => $this->author,
      '__date' => $this->date_string,
      '__description' => $this->description,
      '__homepage' => $this->url,
      '__name' => $this->name,
      '__url' => $this->url,
      '__version' => $this->version,
    );

    $info = $this->infoFile->load()->getJson(TRUE);
    if (isset($info['title'])) {
      $token_map['__title'] = $info['title'];
    }
    $code = str_replace(array_keys($token_map), array_values($token_map), $this->getSourceCode());

    // Replace the year which will appear as '__year' or '2015__year'.
    $code = preg_replace_callback('/(\s\d{2,4}?)__year/', function ($matches) {
      $years = array();
      isset($matches[1]) && $years[] = $matches[1];
      $years[] = date('Y');

      return implode('-', $years);
    }, $code);

    $this->setSourceCode($code);
    $this->addMessage('tokens have been replaced.');

    return $this;
  }

  /**
   * Define the distribution directory.
   *
   * @param string $dist
   *   A relative or absolute path to the distribution directory.
   */
  public function setDistributionDir(string $dist) {
    $dist = $this->resolve($dist);
    $this->pathToDist = $dist;
  }

  /**
   * Save the source file to the distribution directory.
   *
   * If the distribution directory does not yet exist, it will be created.
   * This will silently overwrite existing files.
   *
   * @return \AKlump\WebPackage\HookService
   * @throws \AKlump\WebPackage\BuildFailException
   */
  public function saveToDist() {
    if (is_null($this->pathToDist)) {
      throw new BuildFailException("You must use setDistributionDir() to specifiy a distribution directory.");
    }
    $to = FilePath::create($this->pathToDist . '/' . $this->sourceFile->getBasename());

    // We will allow overwrite of 'dist' files automatically.
    $to->exists() && $to->destroy();
    $to->parents();

    return $this->saveTo($this->pathToDist);
  }

  /**
   * Overwrite the last loaded file with $this->sourceCode.
   *
   * @return \AKlump\WebPackage\HookService
   * @throws \AKlump\WebPackage\BuildFailException
   */
  public function saveReplacingSourceFile() {
    $this->sourceFile->exists() && $this->sourceFile->destroy();

    return $this->saveTo(dirname($this->sourceFile->getPath()));
  }

  /**
   * Save the source file to a directory.
   *
   * Will fail if file already exists.
   *
   * @param string $dir
   *
   * @return $this
   * @throws \AKlump\WebPackage\BuildFailException
   *   If the file already exists.
   *   If the destination is the same as the source.
   *   If the destination directory doesn't exist.
   */
  public function saveTo(string $dir = 'dist') {
    $to_dir = FilePath::create($this->resolve($dir));
    if (!$to_dir->exists()) {
      throw new BuildFailException("Cannot save to \"{$this->relativize($to_dir->getPath())}\"; it does not exist.");
    }
    $to = $to_dir->to($this->sourceFile->getBasename())
      ->put($this->sourceFile->get());
    if ($this->sourceFile->exists()
      && ($source = $this->sourceFile->getPath()) === $to->getPath()) {
      throw new BuildFailException("You have asked to save over your source file, which cannot be done: \"{$this->relativize($source)}\".");
    }
    if ($to->exists()) {
      throw new BuildFailException("The output path already exists \"{$this->relativize($to->getPath())}\".");
    }
    $to->put($this->getSourceCode())->save();
    $this->addMessage($this->relativize($to->getPath()) . ' has been saved.');
    $this->sourceFile = NULL;

    return $this;
  }

  /**
   * Return the absolute filepath of a path.
   *
   * @param string filepath
   *   Relative paths are resolved to the directory containing .web_package.
   *
   * @return string
   *   The absolute path.
   */
  protected function resolve($filepath) {
    if (substr($filepath, 0, 1) !== '/') {
      $filepath = $this->pathToInstance . '/' . $filepath;
    }

    return $filepath;
  }

  /**
   * Return the relative path of an absolute path inside the instance.
   *
   * @param string $path
   *   A path that will be made relative if it's inside instance root.
   *
   * @return string
   *   $path made relative to the instance directory if possible.
   */
  protected function relativize(string $path) {
    $remove = rtrim($this->pathToInstance, '/');
    if (strpos($path, $remove) === 0) {
      return substr($path, strlen($remove) + 1);
    }

    return $path;
  }

  /**
   * Echo all accumulated messages to date and flush.
   */
  public function displayMessages() {
    $this->endMessageClause();
    print implode(PHP_EOL, $this->messages) . PHP_EOL;
    $this->messages = [];
  }

  /**
   * Display a message of caution and continue.
   *
   * @param string $message
   *   A message to highlight
   */
  public function caution($message) {
    print Color::wrap('black on yellow', $message) . PHP_EOL;

    return $this;
  }

  /**
   * Display message of danger and continue.
   *
   * @param string $message
   *   A message to highlight
   */
  public function danger($message) {
    print Color::wrap('white on red', $message) . PHP_EOL;

    return $this;
  }

  protected function startMessageClause($message) {
    $this->endMessageClause();
    $this->messages[] = rtrim($message, '.') . ',';
  }

  protected function endMessageClause() {
    if (count($this->messages)) {
      $index = count($this->messages) - 1;
      $this->messages[$index] = rtrim($this->messages[$index], ',.') . '.';
    }
  }

  protected function addMessage($message) {
    $this->messages[] = '... and ' . rtrim($message, '.,') . ',';
  }

  /**
   * Return the value of SourceCode.
   *
   * @return string|null
   */
  public function getSourceCode() {
    return $this->sourceCode;
  }

  /**
   * Set the value of SourceCode.
   *
   * @param string $sourceCode
   *   String to be used for code transformations.
   *
   * @return HookService
   *   Self for chaining.
   */
  public function setSourceCode(string $code) {
    $this->sourceCode = $code;

    return $this;
  }

  /**
   * Minify a file and save to the distribution directory.
   *
   * @param string $source
   *   The source file.
   *
   * @return $this
   * @throws \AKlump\WebPackage\BuildFailException
   */
  public function minifyFile($source) {
    $this->loadFile($source);
    if ($this->sourceFile->getExtension() !== 'js') {
      throw new BuildFailException("Minify does not yet support file types ending in: " . $this->sourceFile->getExtension());
    }
    Bash::exec([
      $this->pathToWebPackage . "/node_modules/.bin/uglifyjs",
      "--compress --mangle --comments",
      "--output=" . ($output = str_replace('.js', '.min.js', $this->sourceFile->getPath())),
      "-- " . $this->sourceFile->getPath(),
    ]);
    $this->addMessage("minified to {$this->relativize($output)}.");

    $this->sourceFile = NULL;

    return $this;
  }

  /**
   * Publish your package to the appropriate service.
   *
   * @throws \AKlump\WebPackage\BuildFailException
   * @throws \AKlump\WebPackage\HookException
   */
  public function publish() {

    // Publish to https://www.npmjs.com/ when we have a package.json file.
    $target = $npm = FilePath::create($this->resolve('package.json'));

    if (!$target->exists()) {
      throw new BuildFailException("Unable to determine publish target.");
    }

    $can_publish = FALSE;
    if ($target === $npm) {
      $this->startMessageClause("Publishing to npmjs.com");
      $command = "npm publish";
      // Make sure we have enough info to publish to https://www.npmjs.com/
      $data = $target->load()->getJson();
      $can_publish = !empty($data->name) && !empty($data->repository) && !empty($data->version) && empty($data->private);
    }

    if (!$can_publish) {
      throw new BuildFailException("Missing critical information necessary to publish.");
    }

    $this->addMessage(Bash::exec($command));

    return $this;
  }

  protected function isUsingGit() {
    return file_exists($this->resolve('.git'));
  }

  /**
   * Compile documentation and to source control (is using).
   *
   * The assumption is that the generated documentation is in "docs".
   *
   * @param string $path_to_generated_docs
   *   Defaults to 'docs'; this will determine success as we look for
   *   $path_to_generated_docs/index.html as a sign of success.
   *
   * @return $this
   * @throws \AKlump\WebPackage\BuildFailException
   */
  public function generateDocumentation($path_to_generated_docs = 'docs') {
    $path_to_generated_docs = rtrim($path_to_generated_docs, '/');
    $commands = [
      "[[ -d {$path_to_generated_docs} ]] && rm -r {$path_to_generated_docs}",
      "(cd documentation && ./core/compile.sh)",
    ];
    echo Bash::exec(implode(';', $commands)) . PHP_EOL;
    $this->scmFilesToAdd[] = $path_to_generated_docs;

    if (!file_exists($path_to_generated_docs . '/index.html')) {
      throw new BuildFailException($path_to_generated_docs . "/index.html was not created.");
    }

    return $this;
  }

  /**
   * Adds any files that were gathered upstream plus those passed to SCM.
   *
   * @param array $files
   *   An optional array of files to add to SCM.
   *
   * @return \AKlump\WebPackage\HookService
   * @throws \AKlump\WebPackage\BuildFailException
   *
   * @see ::documentation
   */
  public function addFilesToScm(array $files) {
    $files = array_unique(array_merge($this->scmFilesToAdd, $files));
    if ($this->isUsingGit()) {
      $this->startMessageClause("Git has been detected");
      foreach ($files as $file) {
        if (is_dir($file)) {
          Bash::exec("(cd \"$file\" && git add .)");
          $this->addMessage('added directory: ' . $this->relativize($file));
        }
        elseif (is_file($file)) {
          Bash::exec("git add \"$file\"");
          $this->addMessage('added file: ' . $this->relativize($file));
        }
      }
    }

    return $this;
  }

  /**
   * Run tests indicated by a test runner file.
   *
   * @param string $path_to_testrunner
   *
   * @return $this
   * @throws \AKlump\WebPackage\BuildFailException
   */
  public function runTests(string $path_to_testrunner) {
    $this->loadFile($path_to_testrunner);
    if ($this->sourceFile->getFilename() !== 'phpunit') {
      throw new BuildFailException("Only PhpUnit (phpunit.xml) is supported at this time; you provided the test runner: \"$path_to_testrunner\".");
    }
    $result = Bash::exec([
      $this->php,
      Bash::which('phpunit'),
      '--configuration',
      $this->resolve($path_to_testrunner),
    ]);
    $this->addMessage($result);

    return $this;
  }

  /**
   * Set the value of Php.
   *
   * @param string $php
   *   Lorem.
   *
   * @return HookService
   *   Lorem.
   */
  public function setPhp(string $php) {
    if (!is_executable($php)) {
      throw new BuildFailException("Missing or non-executable php: \"$php\"");
    }
    $this->php = $php;

    return $this;
  }
}
