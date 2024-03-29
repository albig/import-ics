<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit8ce34275dffb0c62827b713fe577eb47
{
    public static $prefixesPsr0 = array (
        'I' => 
        array (
            'ICal' => 
            array (
                0 => __DIR__ . '/..' . '/johngrogg/ics-parser/src',
            ),
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixesPsr0 = ComposerStaticInit8ce34275dffb0c62827b713fe577eb47::$prefixesPsr0;
            $loader->classMap = ComposerStaticInit8ce34275dffb0c62827b713fe577eb47::$classMap;

        }, null, ClassLoader::class);
    }
}
