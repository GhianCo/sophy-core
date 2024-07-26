<?php

namespace Sophy\Cli\Commands;

use Sophy\App;
use Sophy\Database\DB;
use Sophy\Helpers\File;
use Spatie\Regex\Regex;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;

class MakeModule extends Command {
    protected $templatesDir = '';
    protected $appDir = '';
    protected $infoTable = [];
    protected $output = null;

    protected static $defaultName = "make:module";
    protected static $defaultDescription = "Create a new module";

    protected function configure() {
        $this->addArgument("name", InputArgument::REQUIRED, "Module name");
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $this->output = new ConsoleOutput();
        $name = $input->getArgument("name");

        $this->templatesDir = resourcesDirectory() . '/templates/';
        $this->appDir = App::$root . '/app/';

        $moduleIsValid = $this->validateHasTable($name);

        if ($moduleIsValid) {
            $this->makeActions($name);
            //$this->makeEntity($name);
            //$this->makeDTO($name);
            //$this->makeException($name);
            //$this->makeRepository($name);
            //$this->makeRoute($name);
            //$this->makeServices($name);
            //$this->addToRoute($name);
            //$this->addToRepositoryServiceProvider($name);

            $output->writeln("<info>Module created => $name</info>");

            return Command::SUCCESS;
        }

        return Command::FAILURE;
    }

    private function validateHasTable($name) {
        try {
            /*$query = DB::query('describe ' . $name);
            $dataTable = $query->fetchAll();

            foreach ($dataTable as $index => $value) {
                $data = new \stdClass();
                $data->key = $value['Field'];
                $data->data = $value;
                $this->infoTable[$index] = $data;
            }*/
            return true;
        } catch (\Exception $exception) {
            $this->output->writeln('<error>' . $exception->getMessage() . '</error>');
            return false;
        }
    }

    private function makeActions($name) {
        $source = $this->templatesDir . 'ObjectBaseActions';
        $target = $this->appDir .'Actions/'. ucfirst($name);

        File::recursiveCopy($source, $target);

        File::replaceFileContent($target . '/Create.php', $name);
        //File::replaceFileContent($target . '/CreateValidator.php', $name);
        File::replaceFileContent($target . '/GetAll.php', $name);
        //File::replaceFileContent($target . '/GetByBody.php', $name);
        //File::replaceFileContent($target . '/GetByQuery.php', $name);
        File::replaceFileContent($target . '/GetOne.php', $name);
        File::replaceFileContent($target . '/Update.php', $name);
        File::replaceFileContent($target . '/Delete.php', $name);
    }

    private function makeEntity($name) {
        $__srcEntity = PHP_EOL;
        $__srcEntity .= PHP_EOL;
        $__srcEntity .= "namespace App\\" . ucfirst($name) . "\Domain\Entities;" . PHP_EOL;
        $__srcEntity .= PHP_EOL;
        $__srcEntity .= "use Sophy\Domain\BaseEntity;" . PHP_EOL;
        $__srcEntity .= PHP_EOL;
        $__srcEntity .= "final class " . ucfirst($name) . " extends BaseEntity" . PHP_EOL;
        $__srcEntity .= "{" . PHP_EOL;
        $__srcEntity .= PHP_EOL;
        $__srcEntity .= "    protected \$fillable = [" . PHP_EOL;
        foreach ($this->infoTable as $indexField => $field) {
            $__srcEntity .= "        '" . $this->infoTable[$indexField]->key . "'," . PHP_EOL;
        }
        $__srcEntity .= "    ];" . PHP_EOL;

        $__srcEntity .= PHP_EOL;

        foreach ($this->infoTable as $indexField => $field) {
            $field = $this->infoTable[$indexField]->key;
            $__srcEntity .= "    public function set" . ucwords($field) . "($" . $field . "){ " . PHP_EOL;
            $__srcEntity .= "        \$this->setAttribute('" . $field . "', \$" . $field . ");" . PHP_EOL;
            $__srcEntity .= "    }" . PHP_EOL;
            $__srcEntity .= PHP_EOL;

            $__srcEntity .= "    public function get" . ucwords($field) . "(){ " . PHP_EOL;
            $__srcEntity .= "        return \$this->getAttribute('" . $field . "');" . PHP_EOL;
            $__srcEntity .= "    }" . PHP_EOL;
            $__srcEntity .= PHP_EOL;
        }
        $__srcEntity .= "}" . PHP_EOL;

        $__srcEntity = "<?php " . $__srcEntity . "?>";

        $dir = $this->appDir . ucfirst($name) . '/Domain/Entities';

        @mkdir($dir, 0777, true);

        File::writeFile($__srcEntity, $dir . '/' . ucfirst($name) . ".php");
    }

    private function makeDTO($name) {
        $__srcEntity = PHP_EOL;
        $__srcEntity .= PHP_EOL;
        $__srcEntity .= "namespace App\\" . ucfirst($name) . "\Application\DTO;" . PHP_EOL;
        $__srcEntity .= PHP_EOL;
        $__srcEntity .= "final class " . ucfirst($name) . "DTO" . PHP_EOL;
        $__srcEntity .= "{" . PHP_EOL;
        foreach ($this->infoTable as $indexField => $field) {
            $__srcEntity .= "    public $" . $this->infoTable[$indexField]->key . ";" . PHP_EOL;
        }

        $__srcEntity .= "}" . PHP_EOL;

        $__srcEntity = "<?php " . $__srcEntity . "?>";

        $dir = $this->appDir . ucfirst($name) . '/Application/DTO';

        @mkdir($dir, 0777, true);

        File::writeFile($__srcEntity, $dir . '/' . ucfirst($name) . "DTO.php");
    }

    private function makeException($name) {
        $source = $this->templatesDir . 'ObjectbaseException.php';
        $target = $this->appDir . ucfirst($name) . '/Domain/Exceptions/' . ucfirst($name) . 'Exception.php';

        @mkdir($this->appDir . ucfirst($name) . '/Domain');
        @mkdir($this->appDir . ucfirst($name) . '/Domain/Exceptions');
        copy($source, $target);

        File::replaceFileContent($target, $name);
    }

    private function makeRepository($name) {
        $iSource = $this->templatesDir . 'IObjectbaseRepository.php';
        $source = $this->templatesDir . 'ObjectbaseRepository.php';

        @mkdir($this->appDir . ucfirst($name) . '/Infrastructure');
        $iTarget = $this->appDir . ucfirst($name) . '/Domain/I' . ucfirst($name) . 'Repository.php';
        $target = $this->appDir . ucfirst($name) . '/Infrastructure/' . ucfirst($name) . 'RepositoryMysql.php';
        copy($iSource, $iTarget);
        copy($source, $target);

        File::replaceFileContent($iTarget, $name);
        File::replaceFileContent($target, $name);
    }

    private function makeRoute($name) {
        $source = $this->templatesDir . 'ObjectbaseRoute.php';
        $target = $this->appDir . ucfirst($name) . '/' . ucfirst($name) . 'Routes.php';
        copy($source, $target);

        File::replaceFileContent($target, $name);
    }

    private function makeServices($name) {
        $source = $this->templatesDir . 'ObjectbaseServices';
        $target = $this->appDir . ucfirst($name) . '/Application/Services';

        File::recursiveCopy($source, $target);

        File::replaceFileContent($target . '/Base.php', $name);
        File::replaceFileContent($target . '/CreateService.php', $name);
        File::replaceFileContent($target . '/FindService.php', $name);
        File::replaceFileContent($target . '/UpdateService.php', $name);
        File::replaceFileContent($target . '/DeleteService.php', $name);
    }

    //Todo: Falta validar al no existir el archivo
    private function addToRoute($name) {
        $locationRouteFile = App::$root . '/routes/api.php';
        $routeModule = ucfirst($name) . 'Routes::group($group);';
        $useRouteModule = 'use App\\' . ucfirst($name) . '\\' . ucfirst($name) . 'Routes;';

        if (file_exists($locationRouteFile) && File::stringInFileFound($locationRouteFile, $routeModule)) {
            return false;
        }

        $fileRoutesApi = @fopen($locationRouteFile, 'r');

        $dir = App::$root . '/routes';

        if ($fileRoutesApi) {
            $startFunctionFound = false;
            $endFunctionFound = false;

            $routeApiLines = '';
            while (!feof($fileRoutesApi)) {
                $currentLine = fgets($fileRoutesApi);
                if (!$startFunctionFound) {
                    //$startFunctionFound = Regex::match('/function\s*([A-z0-9]+)?\s*\((?:[^)(]+|\((?:[^)(]+|\([^)(]*\))*\))*\)\s*\{(?:[^}{]+|\{(?:[^}{]+|\{[^}{]*\})*\})/', $currentLine)->hasMatch();
                }

                if ($startFunctionFound && !$endFunctionFound) {
                    //$endFunctionFound = Regex::match('/\}/', $currentLine)->hasMatch();
                }

                if ($startFunctionFound && $endFunctionFound) {
                    $routeApiLines .= '    ' . $routeModule . PHP_EOL;

                    preg_match_all('/^(.*\buse\b.*)$/m', $routeApiLines, $allOperatorUse);

                    if (count($allOperatorUse)) {
                        $lastOperatorFound = $allOperatorUse[count($allOperatorUse) - 1];
                        $lastOperatorFound = $lastOperatorFound[count($lastOperatorFound) - 1];
                        $routeApiLines = str_replace(trim($lastOperatorFound), trim($lastOperatorFound . PHP_EOL . $useRouteModule), $routeApiLines);
                    }
                    $startFunctionFound = false;
                    $endFunctionFound = false;
                }
                $routeApiLines .= $currentLine;
            }

            @mkdir($dir, 0777, true);

            File::writeFile($routeApiLines, $dir . '/api.php');
        } else {
            $__srcEntity = PHP_EOL;
            $__srcEntity .= PHP_EOL;
            $__srcEntity .= "use App\DefaultAction;" . PHP_EOL;
            $__srcEntity .= "use Sophy\Routing\Route;" . PHP_EOL;
            $__srcEntity .= PHP_EOL;
            $__srcEntity .= "Route::get('/', DefaultAction::class);" . PHP_EOL;
            $__srcEntity .= PHP_EOL;
            $__srcEntity .= "Route::group('/api', function (\$group) {" . PHP_EOL;
            $__srcEntity .= PHP_EOL;
            $__srcEntity .= "});" . PHP_EOL;

            $__srcEntity = "<?php " . $__srcEntity;

            @mkdir($dir, 0777, true);

            File::writeFile($__srcEntity, $dir . '/api.php');

            $this->addToRoute($name);
        }
    }

    private function addToRepositoryServiceProvider($name) {
        $locationRepositoryServiceProviderFile = App::$root . '/app/Providers/RepositoryServiceProvider.php';
        $repositoryServiceProviderModule = 'App::$container->set(I' . ucfirst($name) . 'Repository::class, \DI\autowire(' . ucfirst($name) . 'RepositoryMysql::class)->method(\'setTable\', \'' . $name . '\'));';
        $useRepositoryServiceProviderModule = 'use App\\' . ucfirst($name) . '\Domain\I' . ucfirst($name) . 'Repository;' .PHP_EOL. 'use App\\' . ucfirst($name) . '\Infrastructure\\' . ucfirst($name) . 'RepositoryMysql;';

        if (file_exists($locationRepositoryServiceProviderFile) && File::stringInFileFound($locationRepositoryServiceProviderFile, $repositoryServiceProviderModule)) {
            return false;
        }

        $fileRepositoryServiceProviderApi = @fopen($locationRepositoryServiceProviderFile, 'r');

        $dir = App::$root . '/app/Providers';

        if ($fileRepositoryServiceProviderApi) {
            $startFunctionFound = false;
            $endFunctionFound = false;

            $repositoryServieProviderApiLines = '';

            while (!feof($fileRepositoryServiceProviderApi)) {
                $currentLine = fgets($fileRepositoryServiceProviderApi);
                if (!$startFunctionFound) {
                    //$startFunctionFound = Regex::match('/^.*\bfunction\b.*$/m', $currentLine)->hasMatch();
                }

                if ($startFunctionFound && !$endFunctionFound) {
                    //$endFunctionFound = Regex::match('/\}/', $currentLine)->hasMatch();
                }

                if ($startFunctionFound && $endFunctionFound) {
                    $repositoryServieProviderApiLines .= '        ' . $repositoryServiceProviderModule . PHP_EOL;

                    preg_match_all('/^(.*\buse\b.*)$/m', $repositoryServieProviderApiLines, $allOperatorUse);

                    if (count($allOperatorUse)) {
                        $lastOperatorFound = $allOperatorUse[count($allOperatorUse) - 1];
                        $lastOperatorFound = $lastOperatorFound[count($lastOperatorFound) - 1];
                        $repositoryServieProviderApiLines = str_replace(trim($lastOperatorFound), trim($lastOperatorFound . PHP_EOL . $useRepositoryServiceProviderModule), $repositoryServieProviderApiLines);
                    }
                    $startFunctionFound = false;
                    $endFunctionFound = false;
                }
                $repositoryServieProviderApiLines .= $currentLine;
            }

            @mkdir($dir, 0777, true);

            File::writeFile($repositoryServieProviderApiLines, $dir . '/RepositoryServiceProvider.php');
        }
    }
}
