<?php

namespace Sophy;

use function DI\autowire;

class Container {
    public static function singleton(string $class, $build = null) {
        if (is_null($build)) {
            App::$container->set($class, $build);
            return new $class();
        }
        if (is_string($build)) {
            App::$container->set($class, autowire($build));
            return new $build();
        } elseif (is_callable($build)) {
            App::$container->set($class, $build());
            return $build();
        }
    }

    public static function resolve(string $class) {
        return App::$container->get($class) ?? null;
    }
}