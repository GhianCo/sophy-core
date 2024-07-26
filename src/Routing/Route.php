<?php

namespace Sophy\Routing;

use Slim\App as Router;
use Sophy\Rounting\Handlers\HttpErrorHandler;
use Sophy\Routing\Handlers\HttpShutdownHandler;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Slim\Factory\ServerRequestCreatorFactory;

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
