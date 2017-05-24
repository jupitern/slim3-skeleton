<?php

namespace App\Console;
use Lib\Framework\DbSchema\DbSchema;
use Lib\Utils\Inflector;
use App\Model\AppAction;

class CodeGen extends Command
{

	/** @var \Illuminate\Database\Connection */
	private $db;

	public function model($tableName, int $saveFile = 0)
	{
		$app = \Lib\Framework\App::instance();
		$dbSchema = new DbSchema();

		$modelName = Inflector::singularize($tableName);
		$columns = $dbSchema->getColumns('Users', 'dbo');
		$this->getColumnRules($columns);

		$relations = [];
		$relations['hasOne'] = $dbSchema->getRelationsHasOne($tableName);
		$relations['hasMany'] = $dbSchema->getRelationsHasMany($tableName);
		$relations['manyMany'] = $dbSchema->getRelationsHasManyMany($tableName);

		if ($columns === null || !count($columns)) {
			return "Error: table '$tableName' not found or driver not supported";
		}

		$fileContent = $this->view->render('console::codegen/modelTemplate', [
			'tableName' => $tableName,
			'modelName' => $modelName,
			'columns' => $columns,
			'relations' => $relations,
		]);

		if ($saveFile === 1) {
			file_put_contents(APP_PATH.'Model'.DS.$modelName.'.php', '<?php '.$fileContent);
			return "model file saved!";
		}

		return $fileContent;
	}


	public function newActions($path = APP_PATH . 'Http', $baseNamespace = 'App\\Http\\App\\')
	{
		echo "adding new actions...\n";

		$newActions = array();
		$allFiles = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path));
		$phpFiles = new \RegexIterator($allFiles, '/\.php$/');

		foreach ($phpFiles as $phpFile) {
			$content = file_get_contents($phpFile->getRealPath());
			$tokens = token_get_all($content);

			$namespace = '';
			for ($index = 0; isset($tokens[$index]); $index++) {
				if (!isset($tokens[$index][0])) {
					continue;
				}
				if (T_NAMESPACE === $tokens[$index][0]) {
					$index += 2; // Skip namespace keyword and whitespace
					while (isset($tokens[$index]) && is_array($tokens[$index])) {
						$namespace .= $tokens[$index++][1];
					}
				}
				if (T_CLASS === $tokens[$index][0]) {
					$index += 2; // Skip class keyword and whitespace
					$className = $namespace.'\\'.$tokens[$index][1];
					$class = new \ReflectionClass($className);

					foreach ($class->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
						if ($method->name == '__construct') continue;

						$action = str_replace('\\', '/', str_replace($baseNamespace, '', $method->class)) . '/' . $method->name;
						$appAction = AppAction::where('Uri', '=', $action)->first();

						if (!$appAction) {
							echo $action ."\n";
							$appAction = new AppAction();
							$appAction->Uri = $action;
							$appAction->AuthRequired = 1;
							$appAction->save();
						}
					}
				}
			}
		}

		echo "\nfinished!";
	}


	private function getColumnRules(array $columns)
	{
		$validationRules = [];
		foreach ($columns as $column) {
			$column->Rules = [];
			if ((int)$column->PrimaryKey) continue;

			if ($column->PhpType == 'int') {
				$column->Rules[] = "intVal()";
			}
			if ($column->PhpType == 'float') {
				$column->Rules[] = "floatVal()";
			}
			if ((int)$column->MaxLength > 0) {
				if ($column->IsNullable == 0 && $column->DefaultValue === NULL) {
					$column->Rules[] = "notBlank()";
				}
				$column->Rules[] = "length(0,{$column->MaxLength})";
			}
		}

		return $validationRules;
	}


}