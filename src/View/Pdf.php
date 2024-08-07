<?php

namespace Sophy\View;

use Sophy\App;
use Sophy\Exceptions\NotFoundException;
use Sophy\Helpers\File;

class Pdf implements ViewStrategy {

    protected string $name;
    protected string $outputDest;

    public function __construct(string $name, $outputDest = 'I')
    {
        $this->name = $name;
        $this->outputDest = $outputDest;
    }

    public function render(string $pathFile, array $params = [])
    {
        $namePdf = $this->name . '.pdf';
        $pathFile = str_replace("#format#", 'pdf', $pathFile) . '.php';
        if (!file_exists($pathFile)) {
            throw NotFoundException::showMessage('El reporte solicitado no existe.');
        }
        
        $content = File::fileOutput($pathFile, $params);
        $pdf = app('PDF');
        $pdf->WriteHTML($content);
        if ($this->outputDest  == 'F'){
            $dir = App::$root . "/storage/App/pdf";
            @mkdir($dir, 0777, true);
            $namePdf = "$dir/$namePdf";
        }
        $pdf->Output($namePdf, $this->outputDest);
    }
}