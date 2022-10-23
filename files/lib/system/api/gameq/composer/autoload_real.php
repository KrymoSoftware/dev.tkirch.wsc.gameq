<?php

// autoload_real.php @generated by Composer

class ComposerAutoloaderInit71c93f58b0a9c0299faa8d03db486d2f
{
    private static $loader;

    public static function loadClassLoader($class)
    {
        if ('Composer\Autoload\ClassLoader' === $class) {
            require __DIR__ . '/ClassLoader.php';
        }
    }

    /**
     * @return \Composer\Autoload\ClassLoader
     */
    public static function getLoader()
    {
        if (null !== self::$loader) {
            return self::$loader;
        }

        require __DIR__ . '/platform_check.php';

        spl_autoload_register(array('ComposerAutoloaderInit71c93f58b0a9c0299faa8d03db486d2f', 'loadClassLoader'), true, false);
        self::$loader = $loader = new \Composer\Autoload\ClassLoader(\dirname(__DIR__));
        spl_autoload_unregister(array('ComposerAutoloaderInit71c93f58b0a9c0299faa8d03db486d2f', 'loadClassLoader'));

        require __DIR__ . '/autoload_static.php';
        call_user_func(\Composer\Autoload\ComposerStaticInit71c93f58b0a9c0299faa8d03db486d2f::getInitializer($loader));

        $loader->register(false);

        return $loader;
    }
}
