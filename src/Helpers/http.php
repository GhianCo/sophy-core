<?php

use Sophy\Http\Response;

function json(array $data): Response {
    return Response::json($data);
}

function view(string $view, array $params = [], string $layout = null): Response {
    return Response::view($view, $params, $layout);
}