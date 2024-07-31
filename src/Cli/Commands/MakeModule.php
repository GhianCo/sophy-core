<?php

namespace Sophy\Cli\Commands;

use PhpParser\Error;
use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;
use PhpParser\ParserFactory;
use Sophy\App;
use Sophy\Helpers\File;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;
use PhpParser\PrettyPrinter;
use PhpParser\Node\Expr\Closure;
use PhpParser\NodeFinder;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Expression;

class MakeModule extends Command
{
    protected $templatesDir = '';
    protected $appDir = '';
    protected $infoTable = [];
    protected $output = null;

    public static $moduleName;

    protected static $defaultName = "make:module";
    protected static $defaultDescription = "Create a new module";

    protected function configure()
    {
        $this->addArgument("name", InputArgument::REQUIRED, "Module name");
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->output = new ConsoleOutput();
        self::$moduleName = $input->getArgument("name");

        $this->templatesDir = resourcesDirectory() . '/templates/';
        $this->appDir = App::$root . '/app/';

        $moduleIsValid = $this->validateHasTable();

        if ($moduleIsValid) {
            $this->makeActions();
            $this->makeEntityAndModel();
            $this->makeRoute();
            $this->addToRoute();

            $output->writeln("<info>Module created =>" . self::$moduleName . "</info>");

            return Command::SUCCESS;
        }

        return Command::FAILURE;
    }

    private function validateHasTable()
    {
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

    private function makeActions()
    {
        $source = $this->templatesDir . 'ObjectBaseActions';
        $target = $this->appDir . 'Actions/' . ucfirst(self::$moduleName);

        File::recursiveCopy($source, $target);

        File::replaceFileContent($target . '/Create.php', self::$moduleName);
        //File::replaceFileContent($target . '/CreateValidator.php', self::$moduleName);
        File::replaceFileContent($target . '/GetAll.php', self::$moduleName);
        //File::replaceFileContent($target . '/GetByBody.php', self::$moduleName);
        //File::replaceFileContent($target . '/GetByQuery.php', self::$moduleName);
        File::replaceFileContent($target . '/GetOne.php', self::$moduleName);
        File::replaceFileContent($target . '/Update.php', self::$moduleName);
        File::replaceFileContent($target . '/Delete.php', self::$moduleName);
    }

    private function makeEntityAndModel()
    {
        $__srcEntityModel = PHP_EOL;
        $__srcEntityModel .= PHP_EOL;
        $__srcEntityModel .= "namespace App\\Entity;" . PHP_EOL;
        $__srcEntityModel .= PHP_EOL;
        $__srcEntityModel .= "use SophyDB\Model;" . PHP_EOL;
        $__srcEntityModel .= PHP_EOL;
        $__srcEntityModel .= "abstract class " . ucfirst(self::$moduleName) . "Entity extends Model" . PHP_EOL;
        $__srcEntityModel .= "{" . PHP_EOL;
        $__srcEntityModel .= "    protected \$table = '" . self::$moduleName . "';" . PHP_EOL;
        $__srcEntityModel .= "    protected \$primaryKey = '" . self::$moduleName . "_id';" . PHP_EOL;
        $__srcEntityModel .= PHP_EOL;
        $__srcEntityModel .= "    protected \$fillable = [];" . PHP_EOL;
        $__srcEntityModel .= "}";
        $__srcEntityModel .= PHP_EOL;

        $__srcEntityModel = "<?php " . $__srcEntityModel . "?>";

        $dir = $this->appDir . 'Entity';

        @mkdir($dir, 0777, true);

        File::writeFile($__srcEntityModel, $dir . '/' . ucfirst(self::$moduleName) . "Entity.php");

        $__srcEntityModel = PHP_EOL;
        $__srcEntityModel .= PHP_EOL;
        $__srcEntityModel .= "namespace App\\Model;" . PHP_EOL;
        $__srcEntityModel .= PHP_EOL;
        $__srcEntityModel .= "use App\Entity\\" . ucfirst(self::$moduleName) . "Entity;" . PHP_EOL;
        $__srcEntityModel .= PHP_EOL;
        $__srcEntityModel .= "class " . ucfirst(self::$moduleName) . " extends " . ucfirst(self::$moduleName) . "Entity" . PHP_EOL;
        $__srcEntityModel .= "{" . PHP_EOL;
        $__srcEntityModel .= "}";
        $__srcEntityModel .= PHP_EOL;

        $__srcEntityModel = "<?php " . $__srcEntityModel . "?>";

        $dir = $this->appDir . 'Model';

        @mkdir($dir, 0777, true);

        File::writeFile($__srcEntityModel, $dir . '/' . ucfirst(self::$moduleName) . ".php");
    }

    private function makeRoute()
    {
        $source = $this->templatesDir . 'ObjectbaseRoute.php';
        $target = $this->appDir . 'routes/' . self::$moduleName . '_route.php';
        copy($source, $target);

        File::replaceFileContent($target, self::$moduleName);
    }

    //Todo: Falta validar al no existir el archivo
    private function addToRoute()
    {
        $locationRouteFile = App::$root . '/routes/api.php';

        $code = file_get_contents($locationRouteFile);

        $parser = (new ParserFactory())->createForNewestSupportedVersion();
        try {
            $ast = $parser->parse($code);
        } catch (Error $error) {
            echo "Parse error: {$error->getMessage()}\n";
            return;
        }
        $nodeFinder = new NodeFinder;

        $apiRoutes = $nodeFinder->find($ast, function (Node $node) {
            return $node instanceof Node\Scalar\String_ && $node->value == '/' . MakeModule::$moduleName . '_route.php';
        });

        if (count($apiRoutes)) {
            return false;
        }

        $traverser = new NodeTraverser();
        $traverser->addVisitor(new class extends NodeVisitorAbstract
        {
            public function enterNode(Node $node)
            {
                if ($node instanceof Closure) {
                    $requireExpr = new FuncCall(
                        new Node\Name('require'),
                        [new Variable('routesDirectory . \'/' . MakeModule::$moduleName . '_route.php\'')]
                    );

                    $callExpr = new FuncCall(
                        $requireExpr,
                        [
                            new Node\Arg(
                                new Variable('group')
                            )
                        ]
                    );

                    $newNode = new Expression($callExpr);
                    $node->stmts[] = $newNode; // Añadir al principio
                }
            }
        });

        $ast = $traverser->traverse($ast);

        $prettyPrinter = new PrettyPrinter\Standard;
        $nodes = $prettyPrinter->prettyPrintFile($ast);

        $dir = App::$root . '/routes';

        @mkdir($dir, 0777, true);

        File::writeFile($nodes, $dir . '/api.php');
    }
}
