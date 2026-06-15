<?php

namespace App\Services\command;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class ResourceGenerator
{
    public static function make(string $model)
    {
        $table = Str::snake(Str::pluralStudly($model));

        if (!Schema::hasTable($table)) {
            return "❌ Table '{$table}' does not exist in database!";
        }

        // ✅ المجلد الجديد تحت Admin
        $baseFolder  = app_path("Http/Resources/Admin");
        $modelFolder = "{$baseFolder}/{$model}";

        // إنشاء الفولدر إذا مش موجود
        if (!File::exists($modelFolder)) {
            File::makeDirectory($modelFolder, 0755, true);
        }

        $resourcePath = "{$modelFolder}/{$model}Resource.php";
        $enumsPath    = "{$modelFolder}/{$model}ResourceEnums.php";

        // ✅ إنشاء Resource إذا مش موجود
        if (!File::exists($resourcePath)) {
            $columns = Schema::getColumnListing($table);
            $resourceStub = self::generateResourceStub($model, $columns);
            File::put($resourcePath, $resourceStub);
        }

        // ✅ تعديل الموديل للعلاقات والحقول JSON
        self::updateModelRelations($model, $table);
        // ✅ إنشاء ملف fields للفرونت
        $jsFolder = resource_path("js/forms");
        if (!File::exists($jsFolder)) {
            File::makeDirectory($jsFolder, 0755, true);
        }

        $fieldsPath = "{$jsFolder}/{$model}Fields.js";
        $fieldsStub = self::generateFieldsStub($model, $table);
        File::put($fieldsPath, $fieldsStub);

        // ✅ إنشاء ResourceEnums إذا مش موجود
        // if (!File::exists($enumsPath)) {
        //     $enumsStub = self::generateEnumsStub($model, $table);
        //     File::put($enumsPath, $enumsStub);
        // }

        return "✅ {$model}Resource created and model relations updated successfully inside Admin folder!";
    }

    private static function updateModelRelations($model, $table)
    {
        $modelPath = app_path("Models/{$model}.php");
        if (!File::exists($modelPath)) return;

        $content = File::get($modelPath);
        $columns = Schema::getColumnListing($table);

        // 🧩 JSON Columns
        $jsonColumns = collect($columns)
            ->filter(fn($col) => Schema::getColumnType($table, $col) === 'json')
            ->values()
            ->toArray();

        if (!empty($jsonColumns)) {
            $translatableArray = "protected \$translatable = ['" . implode("','", $jsonColumns) . "'];";

            if (preg_match('/protected \$translatable\s*=\s*\[.*?\];/s', $content)) {
                $content = preg_replace(
                    '/protected \$translatable\s*=\s*\[.*?\];/s',
                    $translatableArray,
                    $content
                );
            } else {
                if (preg_match('/class ' . $model . '.*?{/', $content, $matches, PREG_OFFSET_CAPTURE)) {
                    $pos = $matches[0][1] + strlen($matches[0][0]);
                    $content = substr_replace($content, "\n    {$translatableArray}\n", $pos, 0);
                }
            }
        }

        // 🔗 علاقات belongsTo
        foreach ($columns as $col) {
            if (Str::endsWith($col, '_id')) {
                $relation = Str::camel(Str::replaceLast('_id', '', $col));
                if (Str::contains($content, "function {$relation}(")) continue;

                $relatedModel = Str::studly(Str::replaceLast('_id', '', $col));
                $relationCode = "\n    public function {$relation}()\n    {\n        return \$this->belongsTo({$relatedModel}::class, '{$col}');\n    }\n";
                $content = preg_replace('/}\s*$/', $relationCode . "\n}", $content);
            }
        }

        File::put($modelPath, $content);
    }

    private static function generateResourceStub($model, $columns)
    {
        $className = "{$model}Resource";
        $resourceArray = [];
        foreach ($columns as $col) {
            $resourceArray[] = "            '{$col}' => \$this->{$col},";
        }

        $resourceContent = implode("\n", $resourceArray);

        return "<?php

namespace App\\Http\\Resources\\Admin\\{$model};

use Illuminate\\Http\\Resources\\Json\\JsonResource;

class {$className} extends JsonResource
{
    public function toArray(\$request): array
    {
        return [
{$resourceContent}
        ];
    }
}
";
    }

    private static function generateFieldsStub($model, $table)
    {
        $columns = Schema::getColumnListing($table);
        $fieldsArray = [];

        foreach ($columns as $col) {
            if (in_array($col, ['id', 'created_at', 'updated_at', 'deleted_at'])) {
                continue;
            }

            $type = Schema::getColumnType($table, $col);
            $label = Str::title(str_replace('_', ' ', $col));
            $placeholder = "Enter {$label}";
            $isRequired = true; // ممكن نطوره لاحقًا من FormRequest

            // 🧩 تحديد النوع
            $inputType = match (true) {
                Str::contains($col, ['image', 'img', 'file', 'logo', 'avatar']) => 'image',
                in_array($type, ['boolean', 'tinyint']) => 'boolean',
                in_array($type, ['text', 'longtext']) => 'textarea',
                in_array($type, ['enum']) => 'select',
                in_array($type, ['integer', 'bigint', 'float', 'double', 'decimal']) => 'number',
                Str::contains($col, 'password') => 'password',
                default => 'text'
            };

            $optionsCode = '';
            $enumType = DB::selectOne("
    SELECT data_type as Type 
    FROM information_schema.columns 
    WHERE table_name = ? AND column_name = ?
", [$table, $col])->type ?? '';
            if (Str::startsWith($enumType, 'enum(')) {
                preg_match("/enum\((.*)\)/", $enumType, $matches);
                if (!empty($matches[1])) {
                    $values = str_getcsv(str_replace("'", "", $matches[1]));
                    $options = collect($values)
                        ->map(fn($v) => ['value' => $v, 'label' => ucfirst($v)])
                        ->values()
                        ->toArray();
                    $optionsJson = json_encode($options, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
                    $optionsCode = ",\n      options: " . $optionsJson;
                }
            }

            // 🧩 الصورة تكون دايمًا string
            $isString = $inputType === 'image' ? 'true' : 'false';

            $fieldsArray[] = "  { key: \"{$col}\", label: \"{$label}\", required: {$isRequired}, placeholder: \"{$placeholder}\", type: \"{$inputType}\", isString: {$isString}{$optionsCode} }";
        }

        $fieldsString = implode(",\n", $fieldsArray);

        return "export const fields = [
{$fieldsString}
];";
    }
}
