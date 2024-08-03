<?php

namespace Sophy\Actions;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpNotFoundException;
use Sophy\Exceptions\SophyException;
use Sophy\View\View;

abstract class Action {
    protected Request $request;

    protected Response $response;

    protected array $args;

    /**
     * @throws HttpNotFoundException
     * @throws HttpBadRequestException
     */
    public function __invoke(Request $request, Response $response, array $args): Response {
        $this->request = $request;
        $this->response = $response;
        $this->args = $args;

        try {
            if ($this->request->getAttribute('has_errors')) {
                $errors = $this->request->getAttribute('errors');
                return $this->respondWithError($errors, 'Error de validaciones', 422);
            }

            return $this->action();
        } catch (HttpNotFoundException | SophyException $e) {
            throw new HttpNotFoundException($this->request, $e->getMessage());
        }
    }

    /**
     * @throws HttpNotFoundException
     * @throws HttpBadRequestException
     */
    abstract protected function action(): Response;

    /**
     * @return array|object
     */
    protected function getFormData() {
        return $this->request->getParsedBody();
    }

    /**
     * @return mixed
     * @throws HttpBadRequestException
     */
    protected function resolveArg(string $name) {
        if (!isset($this->args[$name])) {
            throw new HttpBadRequestException($this->request, "Could not resolve argument `{$name}`.");
        }

        return $this->args[$name];
    }

    /**
     * @param array|object|null $data
     */
    protected function respondWithData($data = null, $message = null, $pagination = null, int $code = 200): Response {
        $payload = new ActionPayload($code, $data, $message, $pagination);

        return $this->respond($payload);
    }

    protected function respondWithError($error, $message = null, int $code = 200): Response {
        $payload = new ActionPayload($code, null, $message, null, $error);

        return $this->respond($payload);
    }

    protected function respond(ActionPayload $payload): Response {
        $json = json_encode($payload, JSON_PRETTY_PRINT);
        $this->response->getBody()->write($json);

        return $this->response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($payload->getStatusCode());
    }

    public function view(string $view, array $params = [], string $layout = null): Response {
        $content = app(View::class)->render($view, $params, $layout);
        $this->response->getBody()->write($content);

        return $this->response
            ->withHeader('Content-Type', "text/html");
    }
}
