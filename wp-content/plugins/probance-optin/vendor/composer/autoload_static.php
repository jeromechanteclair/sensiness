<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit5a661d0bb625ee50f81a43e48290bdaa
{
    public static $prefixLengthsPsr4 = array (
        'I' => 
        array (
            'Inc\\' => 4,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Inc\\' => 
        array (
            0 => __DIR__ . '/../..' . '/inc',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit5a661d0bb625ee50f81a43e48290bdaa::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit5a661d0bb625ee50f81a43e48290bdaa::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit5a661d0bb625ee50f81a43e48290bdaa::$classMap;

        }, null, ClassLoader::class);
    }
}
