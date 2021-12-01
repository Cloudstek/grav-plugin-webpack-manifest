<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitd9e18bf1cfd8b90bd3098d3e4e9906bb
{
    public static $prefixLengthsPsr4 = array (
        'G' => 
        array (
            'Grav\\Plugin\\WebpackManifest\\' => 28,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Grav\\Plugin\\WebpackManifest\\' => 
        array (
            0 => __DIR__ . '/../..' . '/classes',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
        'Grav\\Plugin\\WebpackManifestPlugin' => __DIR__ . '/../..' . '/webpack-manifest.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitd9e18bf1cfd8b90bd3098d3e4e9906bb::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitd9e18bf1cfd8b90bd3098d3e4e9906bb::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInitd9e18bf1cfd8b90bd3098d3e4e9906bb::$classMap;

        }, null, ClassLoader::class);
    }
}
