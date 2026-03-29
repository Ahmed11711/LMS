<?php

namespace App\Traits;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

trait TracksFeatureUsage
{
    public static function bootTracksFeatureUsage()
    {
        static::created(function ($model) {
            $model->adjustNumericUsage(1);

            if ($model->tracksStorage() && $model->file_size_mb > 0) {
                $model->adjustStorageUsage($model->file_size_mb);
            }
        });

        static::updated(function ($model) {
            if ($model->tracksStorage()) {
                $oldSize = (float) ($model->getOriginal('file_size_mb') ?? 0);
                $newSize = (float) ($model->file_size_mb ?? 0);
                $difference = $newSize - $oldSize;

                if ($difference != 0) {
                    $model->adjustStorageUsage($difference);
                }
            }
        });

        static::deleted(function ($model) {
            $model->adjustNumericUsage(-1);

            if ($model->tracksStorage() && $model->file_size_mb > 0) {
                $model->adjustStorageUsage($model->file_size_mb * -1);
            }
        });
    }

    protected function adjustNumericUsage($multiplier)
    {
        try {
            DB::table('tenant_feature_usage')
                ->where('feature_slug', $this->getFeatureSlug())
                ->increment('used_amount', $this->getAmountForUsage() * $multiplier);
        } catch (\Exception $e) {
            Log::error("Numeric Usage Error: " . $e->getMessage());
        }
    }

    protected function adjustStorageUsage($amount)
    {
        try {
            DB::table('tenant_feature_usage')
                ->where('feature_slug', 'storage_limit')
                ->increment('used_amount', $amount);
        } catch (\Exception $e) {
            Log::error("Storage Usage Error: " . $e->getMessage());
        }
    }

    public function tracksStorage(): bool
    {
        return array_key_exists('file_size_mb', $this->getAttributes()) || isset($this->file_size_mb);
    }

    public function getAmountForUsage(): int
    {
        return 1;
    }

    abstract public function getFeatureSlug(): string;
}
