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
    private static Request $request;

    private static Router $router;

    public static function load(string $routesDirectory) {
        foreach (glob("$routesDirectory/*.php") as $routes) {
            require_once $routes;
        }
    }

    public static function group(string $uri, $action) {
        return self::$router->group($uri, $action);
    }

    public static function get(string $uri, $action) {
        return self::$router->get($uri, $action);
    }

    public static function post(string $uri, $action) {
        return self::$router->post($uri, $action);
    }

    public static function put(string $uri, $action) {
        return self::$router->put($uri, $action);
    }

    public static function delete(string $uri, $action) {
        return self::$router->delete($uri, $action);
    }

    public static function source()
    {
        self::$router = singleton(Router::class, function () {
            AppFactory::setContainer(app()->container);
            $router = AppFactory::create();
            $router->setBasePath('/' . config("app.path_route"));
            return $router;
        });

        self::$request = singleton(Request::class, function () {
            $serverRequestCreator = ServerRequestCreatorFactory::create();
            return $serverRequestCreator->createServerRequestFromGlobals();
        });
    }

    public function run()
    {
        $env = config('app.env');

        // Create Error Handler
        $errorHandler = new HttpErrorHandler(self::$router->getCallableResolver(), self::$router->getResponseFactory());

        // Create Shutdown Handler
        register_shutdown_function(new HttpShutdownHandler($this->request, $errorHandler, $env == 'dev'));

        // Add Routing Middleware
        self::$router->addRoutingMiddleware();

        // Add Body Parsing Middleware
        self::$router->addBodyParsingMiddleware();

        // Add Error Middleware
        $errorMiddleware = self::$router->addErrorMiddleware($env == 'dev', false, false);
        $errorMiddleware->setDefaultErrorHandler($errorHandler);

        // Run App & Emit Response
        $responseEmitter = new ResponseEmitter();
        $responseEmitter->emit(self::$router->handle($this->request));
    }
}
