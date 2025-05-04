<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MakeModelStructure extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:s-model {name} {--cs= : Optional columns for the migration}'; // متغير name لتحديد اسم الـ Model


    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'إنشاء Model وMigration وController وRequest وResource وRepository';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $name = ucfirst($this->argument('name'));
        $columns = $this->option('cs') ? explode(',', $this->option('cs')) : [];
        if (!File::exists(app_path("Http/Models/{$name}/"))) {
            File::makeDirectory(app_path("Http/Models/{$name}/"), 0755, true);
        }
        // إنشاء الـ Model
        $path = app_path("Http/Models/{$name}/{$name}.php");
        //$ModelPath = app_path("Models/{$ModelName}.php");
        $this->createModel($path, $name , $columns);

        // إنشاء الـ Migration

        $this->createMigration( $name , $columns);

        // إنشاء الـ Controller
        $path = app_path("Http/Models/{$name}/{$name}Controller.php");
        //$controllerName = "{$name}Controller";
        //$controllerPath = app_path("Http/Controllers/{$controllerName}.php");
        $this->createController($path, $name , $columns);

        // إنشاء الـ Resource
        $path = app_path("Http/Models/{$name}/{$name}Resource.php");
        //$resourceName = "{$name}Resource";
        //$resourcePath = app_path("Http/Resources/{$resourceName}.php");
        $this->createResource($path, $name, $columns);

        // إنشاء الـ Repository
        $path = app_path("Http/Models/{$name}/{$name}Repository.php");
        //$repositoryName = "{$name}Repository";
        //$repositoryPath = app_path("Repositories/{$repositoryName}.php");
        $this->createRepository($path, $name);

        $this->info("تم إنشاء جميع المكونات بنجاح لـ {$name}!");
    }

    protected function createRepository($path, $model)
    {
        $modelUppercase = ucfirst($model);
        $content =
        "<?php

        namespace App\Http\Models\\$modelUppercase;
        use App\Lib\Http\HttpStructure\AdvancedRepository;
        use App\Lib\Http\HttpStructure\CustomValidator;

        class {$modelUppercase}Repository extends AdvancedRepository
        {
            public function __construct(array \$fileFields = [])
            {
                parent::__construct(new {$modelUppercase}() , \$fileFields );
                \$this->listedRelations = [];
            }
        }
        ";

        file_put_contents($path, $content);
    }
    protected function createController($path, $model , $columns)
    {
        $modelLowercase = lcfirst($model);
        $modelUppercase = ucfirst($model);

        $columnsDefinition = '';
        $messegs = '';
        foreach ($columns as $column) {
            $columnName = \Illuminate\Support\Str::snake($column);
            $columnsDefinition .= ",\n'$columnName'=>'string'";
            $messegs .=",\n'$columnName'=>'$columnName is not valid'";
        }

        $content =
        "<?php

        namespace App\Http\Models\\$modelUppercase;

        use App\Lib\Http\HttpStructure\Enums\ImagePath;
        use App\Lib\Http\HttpStructure\RecourceController;
        use App\Lib\Http\HttpStructure\Rules\FileOrNameRule;
        use App\Lib\Http\HttpStructure\CustomValidator;
        use App\Lib\Http\HttpStructure\Rules\StringOrNumber;

        class {$modelUppercase}Controller extends RecourceController{

            protected {$modelUppercase}Repository \$rep;

            public function __construct(){
                \$this->resource = new {$modelUppercase}Resource(null);

                \$this->validator = new CustomValidator();

                \$this->validator->fileFields = [];

                \$this->validator->rules = [
                    'id'=>new StringOrNumber()$columnsDefinition
                ];

                \$this->validator->messeges = [
                    'id'=>'id is not valid'$messegs
                ];

                \$this->repository = new {$modelUppercase}Repository(\$this->validator->fileFields);
                \$this->rep = \$this->repository;
            }
        }
        ";

        file_put_contents($path, $content);
    }
    protected function createResource($path, $model, $columns)
    {
        $modelLowercase = lcfirst($model);
        $modelUppercase = ucfirst($model);

        $columnsDefinition = '';
        $columnsDefinitionArray = '';
        foreach ($columns as $column) {
            $columnName = \Illuminate\Support\Str::snake($column);
            $columnsDefinition .=",\n'$columnName'=>\$item->$columnName";
            $columnsDefinitionArray .=",\n'$columnName'=>\$item['$columnName']";
        }

        $content =
        "<?php

        namespace App\Http\Models\\$modelUppercase;

        use App\Lib\Http\HttpStructure\AdvancedResource;

        class {$modelUppercase}Resource extends AdvancedResource{
            protected function paginationResource(\$item):array{
                return [
                    'id'=>\$item->id$columnsDefinition
                ];
            }
            protected function collectionResource(\$item):array{
                return [
                    'id'=>\$item->id$columnsDefinition
                ];
            }
            protected function singleResource(\$item):array{
                return [
                    'id'=>\$item->id$columnsDefinition
                ];
            }
            protected function arrayResource(\$item):array{
                return [
                    'id'=>\$item['id']$columnsDefinition
                ];
            }
        }
        ";

        file_put_contents($path, $content);
    }
    /*protected function createRequest($path, $model)
    {
        $modelLowercase = lcfirst($model);
        $modelUppercase = ucfirst($model);

        $content = "<?php

        namespace App\Http\Requests;

        use App\Lib\Lib;
        use Illuminate\Foundation\Http\FormRequest;

        class {$modelUppercase}Request extends FormRequest{

            public function authorize(): bool
            {
                return true;
            }

            public function rules(): array
            {
                return [

                ];
            }

            public function messages(): array
            {
                return [
                ];
            }
        }
        ";

        file_put_contents($path, $content);
    }*/
    protected function createModel($path, $model , $columns)
    {
        $modelLowercase = lcfirst($model);
        $modelUppercase = ucfirst($model);
        $table = $this->camelToSnakeCase($model).'s';

        $content = "<?php

        namespace App\Http\Models\\$modelUppercase;

        use App\Lib\Http\HttpStructure\AdvancedModel;

        class {$modelUppercase} extends AdvancedModel{
            protected \$table = '{$table}';
            protected \$fillable = " . $this->formatFillableArray($columns) . ";
            protected \$with = [];
        }
        ";

        file_put_contents($path, $content);
    }
    protected function createMigration( $model , $columns)
    {
        $tableName = $this->camelToSnakeCase($model) . 's';

        $columnsDefinition = '';
        foreach ($columns as $column) {
            $columnName = \Illuminate\Support\Str::snake($column);
            $columnsDefinition .= "\$table->string('$columnName');\n                    ";
        }

        $content = "<?php

        use Illuminate\Database\Migrations\Migration;
        use Illuminate\Database\Schema\Blueprint;
        use Illuminate\Support\Facades\Schema;

        return new class extends Migration
        {
            /**
             * Run the migrations.
             */
            public function up(): void
            {
                Schema::create('$tableName', function (Blueprint \$table) {
                    \$table->id();
                    $columnsDefinition
                    \$table->timestamps();
                });
            }

            /**
             * Reverse the migrations.
             */
            public function down(): void
            {
                Schema::dropIfExists('$tableName');
            }
        };

        ";
        $path = database_path('migrations/' . date('Y_m_d_His') . '_create_' . $tableName . '_table.php');
        file_put_contents($path, $content);
    }
    private function formatFillableArray(array $columns): string
    {
        $formattedColumns = array_map(fn($column) => "'" . Str::snake($column) . "'", $columns);
        return '[' . implode(', ', $formattedColumns) . ']';
    }
    function camelToSnakeCase($string) {
        return Str::snake($string) ;
    }

}
