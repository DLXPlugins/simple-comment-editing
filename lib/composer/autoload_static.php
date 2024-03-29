<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitdcff8ee86632d82804efb5de4ffd6f95
{
    public static $prefixLengthsPsr4 = array (
        'D' => 
        array (
            'DLXPlugins\\CommentEditLite\\' => 27,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'DLXPlugins\\CommentEditLite\\' => 
        array (
            0 => __DIR__ . '/../..' . '/includes',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitdcff8ee86632d82804efb5de4ffd6f95::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitdcff8ee86632d82804efb5de4ffd6f95::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInitdcff8ee86632d82804efb5de4ffd6f95::$classMap;

        }, null, ClassLoader::class);
    }
}
