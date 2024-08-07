<?php

namespace Sophy\View;

use App\Consts;
use Sophy\Exceptions\NotFoundException;

class ViewContext
{
    protected string $viewsDirectory;
    private $strategy;

    public function __construct(string $viewsDirectory)
    {
        $this->viewsDirectory = $viewsDirectory;
    }

    public function setStrategy(ViewStrategy $strategy)
    {
        $this->strategy = $strategy;
        return $this;
    }

    public function setViewsDirectory(string $viewsDirectory)
    {
        $this->viewsDirectory = $viewsDirectory;
        return $this;
    }

    public function compile(string $view, array $params = [])
    {
        if ($view == Consts::UNDEFINED || strlen($view) == 0) {
            throw NotFoundException::showMessage('La vista es requerida.');
        }

        $pathFileName = Consts::UNDEFINED;
        $viewsDirectory = $this->viewsDirectory . '/#format#';
        $partsPathView = explode('_', $view);

        switch (count($partsPathView)) {
            case 1:
                $pathFileName = $partsPathView[0];
                break;
            case 2:
                $viewsDirectory .= '/' . $partsPathView[0];
                $pathFileName = $partsPathView[1];
                break;
            case 3:
                $viewsDirectory .= '/' . "{$partsPathView[0]}/$partsPathView[1]";
                $pathFileName = $partsPathView[2];
                break;
            default:
                $pathFileName = $partsPathView[0];
                break;
        }

        return $this->strategy->render("{$viewsDirectory}/$pathFileName", $params);
    }
}
