<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit08edf3cc32e556d1b4fcab6c4b77a06a
{
    public static $prefixLengthsPsr4 = array (
        'S' => 
        array (
            'Symfony\\Component\\Yaml\\' => 23,
        ),
        'A' => 
        array (
            'AKlump\\LoftLib\\' => 15,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Symfony\\Component\\Yaml\\' => 
        array (
            0 => __DIR__ . '/..' . '/symfony/yaml',
        ),
        'AKlump\\LoftLib\\' => 
        array (
            0 => __DIR__ . '/../..' . '/lib/loft_php_lib/dist/src/AKlump/LoftLib',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit08edf3cc32e556d1b4fcab6c4b77a06a::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit08edf3cc32e556d1b4fcab6c4b77a06a::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}