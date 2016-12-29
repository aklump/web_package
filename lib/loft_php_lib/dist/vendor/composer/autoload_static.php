<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit26a4b428a16b347e3e51c0053974b6a6
{
    public static $files = array (
        '3a20cf7b16b25178e2d366da94685852' => __DIR__ . '/../..' . '/src/AKlump/LoftLib/Xml/LoftXmlElement.php',
    );

    public static $prefixLengthsPsr4 = array (
        'S' => 
        array (
            'Symfony\\Component\\Yaml\\' => 23,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Symfony\\Component\\Yaml\\' => 
        array (
            0 => __DIR__ . '/..' . '/symfony/yaml',
        ),
    );

    public static $fallbackDirsPsr4 = array (
        0 => __DIR__ . '/../..' . '/src',
        1 => __DIR__ . '/../..' . '/tests',
    );

    public static $classMap = array (
        'AKlump\\LoftLib\\Code\\EncryptionInterface' => __DIR__ . '/../..' . '/src/AKlump/LoftLib/Code/EncryptionInterface.obf.php',
        'AKlump\\LoftLib\\Code\\EncryptionPassThru' => __DIR__ . '/../..' . '/src/AKlump/LoftLib/Code/EncryptionPassThru.obf.php',
        'AKlump\\LoftLib\\Code\\EncryptionPublic' => __DIR__ . '/../..' . '/src/AKlump/LoftLib/Code/EncryptionPublic.obf.php',
        'AKlump\\LoftLib\\Code\\EncryptionTest' => __DIR__ . '/../..' . '/tests/AKlump/LoftLib/Code/EncryptionTest.php',
        'AKlump\\LoftLib\\Code\\Exposer' => __DIR__ . '/../..' . '/src/AKlump/LoftLib/Code/Exposer.php',
        'AKlump\\LoftLib\\Code\\ExposerTest' => __DIR__ . '/../..' . '/tests/AKlump/LoftLib/Code/ExposerTest.php',
        'AKlump\\LoftLib\\Code\\Grammar' => __DIR__ . '/../..' . '/src/AKlump/LoftLib/Code/Grammar.php',
        'AKlump\\LoftLib\\Code\\GrammarTest' => __DIR__ . '/../..' . '/tests/AKlump/LoftLib/Code/GrammarTest.php',
        'AKlump\\LoftLib\\Code\\Liar' => __DIR__ . '/../..' . '/tests/AKlump/LoftLib/Code/ExposerTest.php',
        'AKlump\\LoftLib\\Code\\PhpDocBlock' => __DIR__ . '/../..' . '/src/AKlump/LoftLib/Code/PhpDocBlock.php',
        'AKlump\\LoftLib\\Code\\PhpDocBlockTest' => __DIR__ . '/../..' . '/tests/AKlump/LoftLib/Code/PhpDocBlockTest.php',
        'AKlump\\LoftLib\\Code\\String' => __DIR__ . '/../..' . '/src/AKlump/LoftLib/Code/String.php',
        'AKlump\\LoftLib\\Code\\Strings' => __DIR__ . '/../..' . '/src/AKlump/LoftLib/Code/Strings.php',
        'AKlump\\LoftLib\\Code\\TransformTest' => __DIR__ . '/../..' . '/tests/AKlump/LoftLib/Code/StringTest.php',
        'AKlump\\LoftLib\\Component\\Bash\\Bash' => __DIR__ . '/../..' . '/src/AKlump/LoftLib/Component/Bash/Bash.php',
        'AKlump\\LoftLib\\Component\\Bash\\BashTest' => __DIR__ . '/../..' . '/tests/AKlump/LoftLib/Component/Bash/BashTest.php',
        'AKlump\\LoftLib\\Component\\Config\\Config' => __DIR__ . '/../..' . '/src/AKlump/LoftLib/Component/Config/Config.php',
        'AKlump\\LoftLib\\Component\\Config\\ConfigBash' => __DIR__ . '/../..' . '/src/AKlump/LoftLib/Component/Config/ConfigBash.php',
        'AKlump\\LoftLib\\Component\\Config\\ConfigBashTest' => __DIR__ . '/../..' . '/tests/AKlump/LoftLib/Component/Config/ConfigBashTest.php',
        'AKlump\\LoftLib\\Component\\Config\\ConfigDrupalInfo' => __DIR__ . '/../..' . '/src/AKlump/LoftLib/Component/Config/ConfigDrupalInfo.php',
        'AKlump\\LoftLib\\Component\\Config\\ConfigDrupalInfoTest' => __DIR__ . '/../..' . '/tests/AKlump/LoftLib/Component/Config/ConfigDrupalInfoTest.php',
        'AKlump\\LoftLib\\Component\\Config\\ConfigFileBasedStorage' => __DIR__ . '/../..' . '/src/AKlump/LoftLib/Component/Config/ConfigFileBasedStorage.php',
        'AKlump\\LoftLib\\Component\\Config\\ConfigIni' => __DIR__ . '/../..' . '/src/AKlump/LoftLib/Component/Config/ConfigIni.php',
        'AKlump\\LoftLib\\Component\\Config\\ConfigIniTest' => __DIR__ . '/../..' . '/tests/AKlump/LoftLib/Component/Config/ConfigIniTest.php',
        'AKlump\\LoftLib\\Component\\Config\\ConfigInterface' => __DIR__ . '/../..' . '/src/AKlump/LoftLib/Component/Config/ConfigInterface.php',
        'AKlump\\LoftLib\\Component\\Config\\ConfigJson' => __DIR__ . '/../..' . '/src/AKlump/LoftLib/Component/Config/ConfigJson.php',
        'AKlump\\LoftLib\\Component\\Config\\ConfigJsonTest' => __DIR__ . '/../..' . '/tests/AKlump/LoftLib/Component/Config/ConfigJsonTest.php',
        'AKlump\\LoftLib\\Component\\Config\\ConfigYaml' => __DIR__ . '/../..' . '/src/AKlump/LoftLib/Component/Config/ConfigYaml.php',
        'AKlump\\LoftLib\\Component\\Config\\ConfigYamlTest' => __DIR__ . '/../..' . '/tests/AKlump/LoftLib/Component/Config/ConfigYamlTest.php',
        'AKlump\\LoftLib\\Component\\Config\\FileBasedConfigTestBase' => __DIR__ . '/../..' . '/tests/AKlump/LoftLib/Component/Config/FileBasedConfigTestBase.php',
        'AKlump\\LoftLib\\Drupal\\DrupalBridge' => __DIR__ . '/../..' . '/src/AKlump/LoftLib/Drupal/DrupalBridge.php',
        'AKlump\\LoftLib\\Messenger\\Messenger' => __DIR__ . '/../..' . '/src/AKlump/LoftLib/Messenger/Messenger.php',
        'AKlump\\LoftLib\\Messenger\\MessengerDrupal' => __DIR__ . '/../..' . '/src/AKlump/LoftLib/Messenger/MessengerDrupal.php',
        'AKlump\\LoftLib\\Messenger\\MessengerHtml' => __DIR__ . '/../..' . '/src/AKlump/LoftLib/Messenger/MessengerHtml.php',
        'AKlump\\LoftLib\\Messenger\\MessengerHtmlTestTest' => __DIR__ . '/../..' . '/tests/AKlump/LoftLib/Messenger/MessengerHtmlTest.php',
        'AKlump\\LoftLib\\Messenger\\MessengerInterface' => __DIR__ . '/../..' . '/src/AKlump/LoftLib/Messenger/MessengerInterface.php',
        'AKlump\\LoftLib\\Messenger\\MessengerShell' => __DIR__ . '/../..' . '/src/AKlump/LoftLib/Messenger/MessengerShell.php',
        'AKlump\\LoftLib\\Messenger\\MessengerShellTestTest' => __DIR__ . '/../..' . '/tests/AKlump/LoftLib/Messenger/MessengerShellTest.php',
        'AKlump\\LoftLib\\Utils\\Blocking' => __DIR__ . '/../..' . '/src/AKlump/LoftLib/Utils/Blocking.php',
        'LoftXmlElement' => __DIR__ . '/../..' . '/src/AKlump/LoftLib/Xml/LoftXmlElement.php',
        'LoftXmlElementTest' => __DIR__ . '/../..' . '/tests/AKlump/LoftLib/Xml/LoftXmlElementTest.php',
        'Symfony\\Component\\Yaml\\Dumper' => __DIR__ . '/..' . '/symfony/yaml/Dumper.php',
        'Symfony\\Component\\Yaml\\Escaper' => __DIR__ . '/..' . '/symfony/yaml/Escaper.php',
        'Symfony\\Component\\Yaml\\Exception\\DumpException' => __DIR__ . '/..' . '/symfony/yaml/Exception/DumpException.php',
        'Symfony\\Component\\Yaml\\Exception\\ExceptionInterface' => __DIR__ . '/..' . '/symfony/yaml/Exception/ExceptionInterface.php',
        'Symfony\\Component\\Yaml\\Exception\\ParseException' => __DIR__ . '/..' . '/symfony/yaml/Exception/ParseException.php',
        'Symfony\\Component\\Yaml\\Exception\\RuntimeException' => __DIR__ . '/..' . '/symfony/yaml/Exception/RuntimeException.php',
        'Symfony\\Component\\Yaml\\Inline' => __DIR__ . '/..' . '/symfony/yaml/Inline.php',
        'Symfony\\Component\\Yaml\\Parser' => __DIR__ . '/..' . '/symfony/yaml/Parser.php',
        'Symfony\\Component\\Yaml\\Unescaper' => __DIR__ . '/..' . '/symfony/yaml/Unescaper.php',
        'Symfony\\Component\\Yaml\\Yaml' => __DIR__ . '/..' . '/symfony/yaml/Yaml.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit26a4b428a16b347e3e51c0053974b6a6::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit26a4b428a16b347e3e51c0053974b6a6::$prefixDirsPsr4;
            $loader->fallbackDirsPsr4 = ComposerStaticInit26a4b428a16b347e3e51c0053974b6a6::$fallbackDirsPsr4;
            $loader->classMap = ComposerStaticInit26a4b428a16b347e3e51c0053974b6a6::$classMap;

        }, null, ClassLoader::class);
    }
}
