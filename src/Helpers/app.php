<?php

use Sophy\App;
use Sophy\Config;
use Sophy\Container;

function app(string $class = App::class)
{
    return Container::resolve($class);
}

function singleton(string $class, $build = null)
{
    return Container::singleton($class, $build);
}

function env(string $variable, $default = null)
{
    return $_ENV[$variable] ?? $default;
}

function config(string $configuration, $default = null)
{
    return Config::get($configuration, $default);
}

function resourcesDirectory(): string
{
    return App::$root . "/resources";
}

function routesDirectory(): string
{
    return App::$root . "/routes";
}

function database()
{
    return app()->database;
}

function isDev()
{
    return $_SERVER['HTTP_HOST'] == 'localhost';
}

function isProd()
{
    return !isDev();
}
