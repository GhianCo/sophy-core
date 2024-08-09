<?php

namespace Sophy\View;

use Sophy\App;
use Sophy\Exceptions\NotFoundException;
use Sophy\Helpers\File;
use Mpdf\Mpdf;

class Pdf implements ViewStrategy
{

    private $pdf;
    protected string $name;
    protected string $outputDest;

    public function __construct(string $name, $outputDest = 'I')
    {
        $this->name = $name;
        $this->outputDest = $outputDest;
        $this->config();
    }

    public function render(string $pathFile, array $params = [])
    {
        $namePdf = $this->name . '.pdf';
        $pathFile = str_replace("#format#", 'pdf', $pathFile) . '.php';
        if (!file_exists($pathFile)) {
            throw NotFoundException::showMessage('El reporte solicitado no existe.');
        }

        $content = File::fileOutput($pathFile, $params);
        $this->pdf->WriteHTML($content);
        if ($this->outputDest  == 'F') {
            $dir = App::$root . "/storage/App/pdf";
            @mkdir($dir, 0777, true);
            $namePdf = "$dir/$namePdf";
        }
        $this->pdf->Output($namePdf, $this->outputDest);
    }

    private function config()
    {
        $paper = "A4";
        $or = "P";
        $margin_left = 10;
        $margin_right = 10;
        $margin_top = 10;
        $margin_bottom = 10;

        if (isset($_GET["paper"])) {
            $paper = $_GET["paper"];
        }

        if (array_key_exists("or", $_GET))
            $or = $_GET["or"];

        if (isset($_GET["or"])) {
            switch ($_GET["or"]) {
                case "P":
                    $or = "P";
                    break;
                case "L":
                    $or = "L";
                    break;
                default:
                    $or = "P";
                    break;
            }
        }

        if (isset($_GET["margen"])) {
            $margin_left = $_GET["margen"];
            $margin_right = $_GET["margen"];
        }
        $this->pdf = new Mpdf(array(
            'mode' => 'c',
            'format' => "$paper-$or",
            'default_font_size' => 0,
            'default_font' => 'Arial',
            'margin_left' => $margin_left,
            'margin_right' => $margin_right,
            'margin_top' => $margin_top,
            'margin_bottom' => $margin_bottom,
            'tempDir' => sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'mpdf'
        ));
        $this->pdf->SetDisplayMode('fullpage');
        $this->pdf->list_indent_first_level = 0;
    }
}
