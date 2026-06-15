<?php

namespace App\Services\command;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RequestGenerator
{
    public static function make(string $model)
    {
        try {
            // 🧠 تحديد الجدول
            $modelClass = "App\\Models\\{$model}";
            if (class_exists($modelClass)) {
                $table = (new $modelClass)->getTable();
            } else {
                $table = Str::snake(Str::pluralStudly($model));
            }

            // ✅ تحديد المسارات
            $baseFolder  = app_path("Http/Requests/Admin");
            $modelFolder = "{$baseFolder}/{$model}";
            File::ensureDirectoryExists($modelFolder);

            $storeRequestPath  = "{$modelFolder}/{$model}StoreRequest.php";
            $updateRequestPath = "{$modelFolder}/{$model}UpdateRequest.php";

            // ❌ تحقق من وجود الجدول
            if (!Schema::hasTable($table)) {
                return "❌ Table '{$table}' does not exist in database!";
            }

            // 🧱 جلب الأعمدة
            $columns = Schema::getColumnListing($table);

            // ⚙️ إنشاء قواعد الفاليديشن
            $generateRules = function ($isUpdate = false) use ($columns, $table, $model) {
                $rules = [];
                $routeParam = Str::camel($model);
                $skip = ['id', 'created_at', 'updated_at', 'deleted_at'];

                foreach ($columns as $col) {
                    if (in_array($col, $skip)) continue;

                    // ✅ PostgreSQL syntax بدل MySQL
                    $info = DB::selectOne("
            SELECT data_type, udt_name, is_nullable, character_maximum_length
            FROM information_schema.columns
            WHERE table_name = ? AND column_name = ?
        ", [$table, $col]);

                    if (!$info) continue;

                    $type     = strtolower($info->udt_name ?? $info->data_type);
                    $nullable = $info->is_nullable === 'YES';
                    $maxLen   = $info->character_maximum_length;
                    $rule     = '';

                    if ($type === 'varchar' || $type === 'bpchar') {
                        $rule = $maxLen ? "string|max:{$maxLen}" : 'string';
                    } elseif (in_array($type, ['text', 'citext'])) {
                        $rule = 'string';
                    } elseif (in_array($type, ['int2', 'int4', 'int8', 'integer', 'bigint', 'smallint'])) {
                        $rule = 'integer';
                    } elseif ($type === 'bool') {
                        $rule = 'boolean';
                    } elseif (in_array($type, ['numeric', 'float4', 'float8', 'decimal'])) {
                        $rule = 'numeric';
                    } elseif (in_array($type, ['date', 'timestamp', 'timestamptz'])) {
                        $rule = 'date';
                    } elseif ($type === 'jsonb' || $type === 'json') {
                        $rule = 'array';
                    }

                    // FK check
                    if (Str::endsWith($col, '_id')) {
                        $related = Str::snake(Str::plural(Str::replaceLast('_id', '', $col)));
                        if (Schema::hasTable($related)) {
                            $rule .= ($rule ? '|' : '') . "exists:{$related},id";
                        }
                    }

                    // File check
                    if (preg_match('/(image|img|file|attachment|photo|picture)/i', $col)) {
                        $rule .= ($rule ? '|' : '') . 'file|max:2048';
                    }

                    // ✅ Unique check بـ PostgreSQL syntax بدل SHOW INDEX
                    $uniqueCheck = DB::selectOne("
            SELECT COUNT(*) as cnt
            FROM information_schema.table_constraints tc
            JOIN information_schema.constraint_column_usage ccu
                ON tc.constraint_name = ccu.constraint_name
            WHERE tc.constraint_type = 'UNIQUE'
              AND tc.table_name = ?
              AND ccu.column_name = ?
        ", [$table, $col]);

                    $isUnique = ($uniqueCheck->cnt ?? 0) > 0;

                    if ($isUnique && !Str::endsWith($col, '_id')) {
                        $rule .= $isUpdate
                            ? ($rule ? '|' : '') . "unique:{$table},{$col},'.\$this->route('{$routeParam}').',id"
                            : ($rule ? '|' : '') . "unique:{$table},{$col}";
                    }

                    $prefix = $isUpdate
                        ? ($nullable ? 'nullable|sometimes' : 'sometimes|required')
                        : ($nullable ? 'nullable' : 'required');

                    $rules[$col] = "{$prefix}" . ($rule ? "|{$rule}" : '');
                }

                return $rules;
            };
            // 🧾 إنشاء الملفات
            $storeRules = $generateRules(false);
            $updateRules = $generateRules(true);

            $storeStub = self::generateStub("{$model}StoreRequest", $storeRules, "Admin\\{$model}");
            $updateStub = self::generateStub("{$model}UpdateRequest", $updateRules, "Admin\\{$model}");

            File::put($storeRequestPath, $storeStub);
            File::put($updateRequestPath, $updateStub);

            return "✅ Requests for {$model} created successfully under Admin folder!";
        } catch (\Throwable $e) {
            return "❌ Error: " . $e->getMessage();
        }
    }

    private static function generateStub($className, $rules, $namespace)
    {
        $rulesString = "";
        foreach ($rules as $key => $value) {
            $rulesString .= "            '{$key}' => '{$value}',\n";
        }

        return "<?php

namespace App\\Http\\Requests\\{$namespace};
use App\Http\Requests\BaseRequest\BaseRequest;
class {$className} extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
{$rulesString}        ];
    }
}
";
    }
}
