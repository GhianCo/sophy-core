<?php

namespace Sophy;

use DI\Container;
use DI\ContainerBuilder;
use Dotenv\Dotenv;

class App
{
    public static string $root;

    public static Container $container;

    public static function bootstrap(string $root): self
    {
        self::$root = $root;

        $containerBuilder = new ContainerBuilder();
        $containerBuilder->useAutowiring(true);

        self::$container = $containerBuilder->build();

        $app = app(self::class);

        return $app->loadConfig()
                   ->runServiceProviders('boot')
                   ->setHttpHandlers()
                   ->runServiceProviders('runtime');
    }

    protected function loadConfig(): self
    {
        date_default_timezone_set(config('app.timezone', 'UTC'));

        Dotenv::createImmutable(self::$root)->load();
        Config::load(self::$root . "/config");

        return $this;
    }

    protected function runServiceProviders(string $type): self
    {
        foreach (config("providers.$type", []) as $provider) {
            (new $provider())->registerServices();
        }

        return $this;
    }
}
