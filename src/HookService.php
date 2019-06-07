<?php

namespace AKlump\WebPackage;

use AKlump\LoftLib\Bash\Bash;
use AKlump\LoftLib\Bash\Color;
use AKlump\LoftLib\Storage\FilePath;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

/**
 * Provide common build functionality to PHP hooks.
 */
class HookService {

  /**
   * The relative (to .web_package parent dir) directory where web_docs core
   * directory is located.  We must cd into this directory to run
   * `core/compile`.
   *
   * @var string
   */
  protected $docsSource = 'documentation';

  /**
   * The relative (to .web_package parent dir) directory where demo source
   * files are expected to reside.
   *
   * @var string
   */
  protected $demoSource = 'documentation/demo';

  protected $php = 'php';

  protected $phpunit = 'phpunit';

  protected $pathToWebPackage;

  protected $pathToInstance;

  protected $pathToDist;

  protected $sourceFile;

  protected $infoFile;

  protected $sourceCode;

  protected $messages = [];

  protected $scmFilesToAdd = [];

  protected $queuedFiles = [];

  protected $tokens = [];

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
    $this->setPhp(Bash::which('php'));
    $this->setPhpUnit(Bash::which('phpunit'));
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

  /**
   * Load a file (with optional contents mutation).
   *
   * @param string $filepath
   *   The filepath to the file to load.
   * @param callable|NULL $mutator
   *   A callback that receives the file contents as loaded from the
   *   filesystem, and must return a string to pass on.  Use this to alter the
   *   contents, for example you could use it to replace the old version string
   *   with `__version` so the tokens can act upon it and update the version
   *   string with the current.
   *
   * @return $this
   * @throws \AKlump\WebPackage\BuildFailException
   */
  public function loadFile($filepath, callable $mutator = NULL) {
    $this->sourceFile = FilePath::create($this->resolve($filepath));
    if (!$this->sourceFile->exists()) {
      throw new BuildFailException("$filepath does not exist.");
    }
    $code = $this->sourceFile->load()->get();
    if ($mutator) {
      $code = $mutator($code, $this->sourceFile->getPath());
    }
    $this->setSourceCode($code);
    $this->startMessageClause($this->relativize($this->sourceFile->getPath()) . ' has been loaded.');

    return $this;
  }

  public function __get($key) {
    return $this->data[$key] ?? NULL;
  }

  /**
   * Add tokens to be used by future methods.
   *
   * @param array $additional_token_map
   *   An array of keys (find) and values (replace).
   *
   * @return $this
   *   Self for chaining.
   */
  public function addTokens(array $additional_token_map = []) {
    $this->tokens += $additional_token_map;

    return $this;
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
    $token_map = $this->prepareTokenMap('__', $additional_token_map);
    $code = str_replace(array_keys($token_map), array_values($token_map), $this->getSourceCode());

    // Replace the year which will appear as '__year' or '2015__year'.
    $code = preg_replace_callback('/(\s\d{2,4}?)\-?__year/', function ($matches) {
      $years = array();
      isset($matches[1]) && $years[] = $matches[1];
      $years[] = date('Y');

      return implode('-', $years);
    }, $code);

    $this->setSourceCode($code);
    $this->addMessage('tokens have been replaced.');

    return $this;
  }

  protected function prepareTokenMap($prefix, array $additional_token_map = []) {
    $token_map = $additional_token_map;
    $token_map += $this->tokens;
    $token_map += array(
      $prefix . 'author' => $this->author,
      $prefix . 'date' => $this->date_string,
      $prefix . 'description' => $this->description,
      $prefix . 'homepage' => $this->url,
      $prefix . 'name' => $this->name,
      $prefix . 'url' => $this->url,
      $prefix . 'version' => $this->version,
    );

    $info = $this->infoFile->load()->getJson(TRUE);
    if (isset($info['title'])) {
      $token_map[$prefix . 'title'] = $info['title'];
    }

    // Have to sort because replacements must happen longest first.
    uksort($token_map, function ($a, $b) {
      return strlen($b) - strlen($a);
    });

    return $token_map;
  }

  /**
   * Set the documentation source directory.
   *
   * @param string $source_dir
   *   The directory where the demo source code is located.
   *
   * @return $this
   *   Self for chaining.
   */
  public function setDocumentationSource($source_dir) {
    if (!$source_dir || !file_exists($source_dir)) {
      throw new \InvalidArgumentException("\"$source_dir\" does not exist.");
    }
    $this->docsSource = $this->resolve($source_dir);

    return $this;
  }

  /**
   * @param string $source_dir
   *   The directory where the demo source code is located.
   *
   * @return $this
   *   Self for chaining.
   */
  public function setDemoSource($source_dir) {
    if (!$source_dir || !file_exists($source_dir)) {
      throw new \InvalidArgumentException("\"$source_dir\" does not exist.");
    }
    $this->demoSource = $this->resolve($source_dir);

    return $this;
  }

  /**
   * Add a file/folder to be copied to the distributed demo.
   *
   * Resolves the source code paths and rewrites the HTML as appropriate.
   *
   * @param string $path
   *   Source path to a file or folder to be included with the demo generation.
   *    This must match exactly what you use in _demo.twig.html to reference
   *   the file, as it is used for string replacement.  Paths are
   *   relative to $this->demoSource/ e.g.
   *   "../../node_modules/bootstrap/dist/css/bootstrap.min.css."
   * @param null $to_relative_path
   *   If this is null, and a file the extension of the filepath will be used
   *   to guess the appropriate location, e.g. file.js will be moved into /js.
   *   Image files will be moved into /images. If you do not wnt this auto
   *   behavior set this to FALSE, if you want to specify a folder in the demo
   *   build directory, then add it without leading slash, and relative to
   *   $path.
   *
   * @return $this
   *   Self for chaining.
   */
  public function addToDemo($path, $to_relative_path = NULL) {
    $source = FilePath::create($this->resolve($this->demoSource . "/$path"));
    if (empty($path) || !$source->exists()) {
      throw new \InvalidArgumentException("\"$path\" does not exist or is invalid.");
    }

    if (is_null($to_relative_path)) {
      if ($source->getType() === FilePath::TYPE_FILE) {
        $extension = $source->getExtension();
        switch (strtolower($extension)) {
          case 'woff':
          case 'eot':
          case 'ttf':
            $to_relative_path = 'fonts';
            break;

          case 'jpeg':
          case 'jpg':
          case 'gif':
          case 'svg':
            $to_relative_path = 'images';
            break;

          case 'css':
          case 'js':
            $to_relative_path = $extension;
            break;

          default:
            break;
        }
        $this->addTokens([
          $path => "$extension/" . $source->getBasename(),
        ]);
      }
    }
    $this->queuedFiles[] = [
      $source,
      $to_relative_path,
    ];

    return $this;
  }

  /**
   * Process the source code as a Twig template.
   *
   * @param array $additional_token_map
   *
   * @return $this
   * @throws \Throwable
   * @throws \Twig_Error_Loader
   * @throws \Twig_Error_Syntax
   */
  public function processWithTwig(array $additional_token_map = []) {
    if (!$this->sourceFile->exists()) {
      throw new \RuntimeException("You must call ::loadFile before calling " . __METHOD__);
    }
    $token_map = $this->prepareTokenMap('', $additional_token_map);
    $loader = new FilesystemLoader($this->sourceFile->getDirname());
    $twig = new Environment($loader);
    $code = $this->getSourceCode();

    $template = $twig->createTemplate($code);

    // Use the actual to set the defaults for the example code.  This may be
    // overidden in the template files.
    $token_map['example_markup'] = $template->renderBlock('actual_markup', $token_map);
    $token_map['example_javascript'] = $template->renderBlock('actual_javascript', $token_map);

    $code = $template->render($token_map);

    $this->setSourceCode($code);
    $this->addMessage('processed with Twig.');
    $this->sourceFile = FilePath::create($this->sourceFile->getDirname() . '/' . $this->sourceFile->getFilename() . '.html');

    return $this;
  }

  /**
   * Define the distribution directory.
   *
   * @param string $dist
   *   A relative or absolute path to the distribution directory.
   *
   * @return \AKlump\WebPackage\HookService
   *   Self for chaining.
   *
   */
  public function setDistributionDir(string $dist) {
    $dist = $this->resolve($dist);
    $this->pathToDist = $dist;

    return $this;
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
   *   The directory into which to save $this->sourceCode
   * @param bool $force
   *   Set to true to erase an existing file.
   *
   * @return $this
   * @throws \AKlump\WebPackage\BuildFailException
   *   If the file already exists.
   *   If the destination is the same as the source.
   *   If the destination directory doesn't exist.
   */
  public function saveTo(string $dir = 'dist', $force = FALSE) {
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
    if (!$force && $to->exists()) {
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
   * @deprecated Use generateDocumentationTo() instead.
   */
  public function generateDocumentation($path_to_generated_docs = 'docs') {
    return $this->generateDocumentationTo($path_to_generated_docs);
  }

  /**
   * Compile documentation and to source control (is using).
   *
   * Source documentation must be in documentation/source.
   *
   * @param string|false $path_to_generated_docs
   *   Defaults to 'docs'; this will determine success as we look for
   *   $path_to_generated_docs/index.html as a sign of success.  Set this to
   *   false if you have disabled the website version, as this will prevent a
   *   failure.
   *
   * @return $this
   * @throws \AKlump\WebPackage\BuildFailException
   */
  public function generateDocumentationTo($path_to_generated_docs = 'docs') {
    $docs_source_dir = $this->resolve($this->docsSource);
    if (!is_dir($docs_source_dir)) {
      throw new \RuntimeException("Missing source directory: " . $docs_source_dir);
    }
    $path_to_generated_docs = $path_to_generated_docs ? $this->resolve($path_to_generated_docs) : FALSE;
    if (trim($docs_source_dir) === trim($path_to_generated_docs)) {
      throw new \InvalidArgumentException("You cannot generate the demo in the source file itself!");
    }

    $commands = [];
    $compile = ['./core/compile.sh'];
    if ($path_to_generated_docs) {
      $compile[] = '--website="' . $path_to_generated_docs . '"';
      $commands[] = "[[ -d \"{$path_to_generated_docs}\" ]] && rm -r {$path_to_generated_docs}";
    }
    $commands[] = "cd $docs_source_dir";
    $commands[] = implode(' ', $compile);

    $result = Bash::exec(implode(';', $commands));
    $this->addMessage($result);

    // TODO Rethink this false idea, kinda stinks. 2019-03-08T10:55, aklump
    if ($path_to_generated_docs) {
      $this->scmFilesToAdd[] = $path_to_generated_docs;

      if (!file_exists($path_to_generated_docs . '/index.html')) {
        throw new BuildFailException($path_to_generated_docs . "/index.html was not created.");
      }
    }

    return $this;
  }

  /**
   * Generates a demo folder using  'demo' as source.
   *
   * This does not empty an existing folder, but does create it if it does not
   * already exist.  The assumption is that there are a number of .twig or
   * .twig.html files in /demo which need to be processed, which also have
   * tokens in them.  If the filename begins with '_' it will not be processed;
   * use such as template references, which will not be converted to pages.
   * Any other files will be copied verbatim.  If you need to process tokens in
   * other files do so manually in the $build chain.
   *
   * @param string $path_to_generated_demo
   *   The output path where the demo is created.  Defaults to dist/demo.
   *
   * @return $this
   *   Self for chaining.
   *
   * Here's a demo source structure tree example:
   *
   *   .
   *   ├── _demo.twig.html
   *   ├── css
   *   │   └── example.css
   *   ├── fonts
   *   │   └── glyphicons-halflings-regular.woff
   *   ├── index.twig
   *   └── loading.twig
   *
   * Here is an example hook file implementation.
   *
   * @code
   *   $build
   *     ->setDemoSource('documentation/demo')
   *     ->addToDemo('../../dist/photo_essay.css')
   *     ->addToDemo('../../dist/jquery.photo_essay.js')
   *     ->addToDemo('../../node_modules/bootstrap/fonts')
   *     ->generateDemoTo('dist/demo')
   *     ->displayMessages();
   * @endcode
   */
  public function generateDemoTo($path_to_generated_demo = 'dist/demo') {
    $demo_source_dir = $this->resolve($this->demoSource);
    if (!is_dir($demo_source_dir)) {
      throw new \RuntimeException("Missing source directory: " . $demo_source_dir);
    }
    $path_to_generated_demo = $this->resolve($path_to_generated_demo);
    if (trim($demo_source_dir) === trim($path_to_generated_demo)) {
      throw new \InvalidArgumentException("You cannot generate the demo in the source file itself!");
    }

    FilePath::ensureDir($path_to_generated_demo);
    $excludes = [];

    // Process all .twig files for tokens and Twig.
    FilePath::create($demo_source_dir)
      ->descendents('/(?:\.twig\.html|\.twig)$/')
      ->each(function ($file) use (&$excludes, $path_to_generated_demo) {
        // Do not process files beginning with '_'. They are meant only for template references.
        if (substr($file->getFilename(), 0, 1) !== '_') {
          $this->loadFile($file->getPath())
            ->processWithTwig()
            ->replaceTokens()
            ->saveTo($path_to_generated_demo, TRUE);
          $excludes[] = $file;
        }
      });

    $this->startMessageClause('Moving files to ' . $this->relativize($path_to_generated_demo) . '.');
    // Then rsync the remaining files and folders.
    $rsync_result = Bash::exec(array_merge([
      "rsync -av",
      $demo_source_dir . '/',
      $path_to_generated_demo . '/',
      '--exclude=_*.twig*',
    ], array_map(function ($file) {
      return '--exclude=' . $file->getBasename();
    }, $excludes)));

    $lines = explode(PHP_EOL, $rsync_result);
    // Remove rsync cruft.
    array_shift($lines);
    array_pop($lines);
    array_pop($lines);
    array_pop($lines);
    array_walk($lines, function ($line) {
      $this->addMessage($line);
    });

    // Copy over added files.
    if ($this->queuedFiles) {
      foreach ($this->queuedFiles as $queued_file) {
        list ($source, $to) = $queued_file;
        $destination_path = $path_to_generated_demo . '/' . $to;
        if ($source->getType() === FilePath::TYPE_DIR) {
          Bash::exec([
            "cp -R",
            $source->getPath(),
            $destination_path,
          ]);
        }
        else {
          FilePath::create($destination_path)
            ->copyFrom($source->getPath());
        }
      }
    }

    $this->scmFilesToAdd[] = $path_to_generated_demo;

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
    if (strpos($this->sourceFile->getFilename(), 'phpunit') === FALSE) {
      throw new BuildFailException("Only filenames matching *phpunit*.xml are supported; you provided the test runner: \"$path_to_testrunner\".");
    }
    $result = Bash::exec([
      $this->php,
      $this->phpunit,
      '--configuration',
      $this->resolve($path_to_testrunner),
    ]);
    $this->addMessage($result);

    return $this;
  }

  /**
   * Set the value of Php.
   *
   * @param string $program_name
   *   The name of the program, e.g. 'php'.
   * @param string $path_to_executable
   *   The path to the program executable.
   *
   * @return HookService
   *   Self for chaining.
   *
   * @throws \AKlump\WebPackage\BuildFailException
   */
  protected function setBashExecutable(string $program_name, string $path_to_executable) {
    if (!is_executable($path_to_executable)) {
      throw new BuildFailException("Missing or non-executable $program_name: \"$path_to_executable\"");
    }
    $this->{$program_name} = $path_to_executable;

    return $this;
  }

  /**
   * Set the php runner.
   *
   * @param string $php
   *
   * @return \AKlump\WebPackage\HookService
   * @throws \AKlump\WebPackage\BuildFailException
   */
  public function setPhp(string $php) {
    return $this->setBashExecutable('php', $php);
  }

  /**
   * Set the php runner.
   *
   * @param string $php
   *
   * @return \AKlump\WebPackage\HookService
   * @throws \AKlump\WebPackage\BuildFailException
   */
  public function setPhpUnit(string $phpunit) {
    return $this->setBashExecutable('phpunit', $phpunit);
  }

}
