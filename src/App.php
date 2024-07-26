<?php

namespace Sophy;

use DI\Container;
use DI\ContainerBuilder;

class App
{
    public static string $root;

    public static Container $container;

    public static function bootstrap(string $root = null): self
    {
        self::$root = $root;

        $containerBuilder = new ContainerBuilder();
        $containerBuilder->useAutowiring(true);

        self::$container = $containerBuilder->build();

        return app(self::class);
    }
}
