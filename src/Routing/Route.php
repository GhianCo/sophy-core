<?php

namespace Sophy\Routing;

class Route
{
    public static function load(string $routesDirectory) {
        foreach (glob("$routesDirectory/*.php") as $routes) {
            require_once $routes;
        }
    }

    public static function group(string $uri, $action) {
        return app()->router->group($uri, $action);
    }

    public static function get(string $uri, $action) {
        return app()->router->get($uri, $action);
    }

    public static function post(string $uri, $action) {
        return app()->router->post($uri, $action);
    }

    public static function put(string $uri, $action) {
        return app()->router->put($uri, $action);
    }

    public static function delete(string $uri, $action) {
        return app()->router->delete($uri, $action);
    }
}
