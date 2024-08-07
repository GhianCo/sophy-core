<?php

namespace Sophy\View;

use Sophy\Exceptions\NotFoundException;
use Sophy\Helpers\File;

class Html implements ViewStrategy {

    protected string $name;
    protected $layout;
    protected string $defaultLayout = "main";
    protected string $contentAnnotation = "@content";

    public function __construct(string $name, $layout = null)
    {
        $this->name = $name;
        $this->layout = $layout;
    }
    public function render(string $pathFilename, array $params = [])
    {
        $pathFilename = str_replace("/#format#", '', $pathFilename) . '.php';
        if (!file_exists($pathFilename)) {
            throw NotFoundException::showMessage('La vista solicitada no existe.');
        }

        if (is_null($this->layout)) {
            return $this->renderView($pathFilename, $params);
        }

        $layoutContent = $this->renderLayout($this->layout ?? $this->defaultLayout);
        $viewContent = $this->renderView($this->name.'.php', $params);

        return str_replace($this->contentAnnotation, $viewContent, $layoutContent);
    }

    protected function renderView(string $view, array $params = []): string
    {
        return File::fileOutput($view, $params);
    }

    protected function renderLayout(string $layout): string
    {
        return File::fileOutput($layout);
    }
}