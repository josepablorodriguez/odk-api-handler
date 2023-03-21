<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit79a6112471662bbd97f9fb9ee26efac8
{
    public static $files = array (
        'd8d7eb01034ebb9eb40bf814c7cb504f' => __DIR__ . '/../..' . '/src/OdkApiHandler.php',
    );

    public static $prefixLengthsPsr4 = array (
        'c' => 
        array (
            'chillerlan\\Settings\\' => 20,
            'chillerlan\\QRCode\\' => 18,
        ),
        'O' => 
        array (
            'OdkApiHandler\\' => 14,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'chillerlan\\Settings\\' => 
        array (
            0 => __DIR__ . '/..' . '/chillerlan/php-settings-container/src',
        ),
        'chillerlan\\QRCode\\' => 
        array (
            0 => __DIR__ . '/..' . '/chillerlan/php-qrcode/src',
        ),
        'OdkApiHandler\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit79a6112471662bbd97f9fb9ee26efac8::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit79a6112471662bbd97f9fb9ee26efac8::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit79a6112471662bbd97f9fb9ee26efac8::$classMap;

        }, null, ClassLoader::class);
    }
}