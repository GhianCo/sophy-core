<?php

namespace Sophy;

use DI\Container;
use DI\ContainerBuilder;
use Dotenv\Dotenv;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App as Router;
use Slim\Factory\AppFactory;
use Slim\Factory\ServerRequestCreatorFactory;
use Sophy\Database\Drivers\IDBDriver;
use Sophy\Http\Handlers\HttpErrorHandler;
use Sophy\Http\Handlers\HttpShutdownHandler;
use Sophy\Http\ResponseEmitter;

class App
{
    public static string $root;

    public static Container $container;

    public Request $request;

    public Router $router;

    public IDBDriver $database;

    public static function bootstrap(string $root): self
    {
        self::$root = $root;

        self::buildContainer();

        $app = app(self::class);

        return $app->loadConfig()
            ->runServiceProviders('boot')
            ->setHttpHandlers()
            ->setUpDatabaseConnection()
            ->runServiceProviders('runtime');
    }

    public static function buildContainer() {
        $containerBuilder = new ContainerBuilder();
        $containerBuilder->useAutowiring(true);

        self::$container = $containerBuilder->build();
    }

    protected function loadConfig(): self
    {
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

    protected function setHttpHandlers(): self
    {
        $this->router = singleton(Router::class, function () {
            AppFactory::setContainer(self::$container);
            $router = AppFactory::create();
            $router->setBasePath('/' . config("app.path_route"));
            return $router;
        });

        $this->request = singleton(Request::class, function () {
            $serverRequestCreator = ServerRequestCreatorFactory::create();
            return $serverRequestCreator->createServerRequestFromGlobals();
        });

        return $this;
    }

    protected function setUpDatabaseConnection(): self {
        $this->database = app(IDBDriver::class);
        return $this;
    }

    public function run()
    {
        $env = config('app.env');

        date_default_timezone_set(config('app.timezone', 'UTC'));

        // Create Error Handler
        $errorHandler = new HttpErrorHandler($this->router->getCallableResolver(), $this->router->getResponseFactory());

        // Create Shutdown Handler
        register_shutdown_function(new HttpShutdownHandler($this->request, $errorHandler, $env == 'dev'));

        // Add Routing Middleware
        $this->router->addRoutingMiddleware();

        // Add Body Parsing Middleware
        $this->router->addBodyParsingMiddleware();

        // Add Error Middleware
        $errorMiddleware = $this->router->addErrorMiddleware($env == 'dev', false, false);
        $errorMiddleware->setDefaultErrorHandler($errorHandler);

        // Run App & Emit Response
        $responseEmitter = new ResponseEmitter();
        $responseEmitter->emit($this->router->handle($this->request));
        $this->database->close();
    }
}
