<?php

namespace Sophy\Validation;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class Validator extends \Valitron\Validator {
    /**
     * The 'errors' attribute name.
     *
     * @var string
     */
    protected $errors_name = 'errors';

    /**
     * The 'has_error' attribute name.
     *
     * @var string
     */
    protected $has_errors_name = 'has_errors';

    /**
     * Create new Validator service provider.
     *
     * @param ServerRequestInterface $request PSR7 request
     */
    public function __construct($request) {
        $params = $request->getParams();
        $routeInfo = isset($request->getAttribute('routeInfo')[2]) ? $request->getAttribute('routeInfo')[2] : [];
        $params = array_merge((array)$routeInfo, $params);
        parent::__construct($params);
    }

    /**
     * Validation middleware invokable class.
     *
     * @param ServerRequestInterface $request PSR7 request
     * @param RequestHandlerInterface $handler RequestHandler
     *
     * @return \Psr\Http\Message\ResponseInterface
     */

    public function __invoke(ServerRequestInterface $request, RequestHandlerInterface $handler) {
        $this->validate();
        $request = $request->withAttribute($this->errors_name, $this->errors());
        $request = $request->withAttribute($this->has_errors_name, $this->hasErrors());
        return $handler->handle($request);
    }

    public function hasErrors() {
        return !empty($this->errors());
    }
}
