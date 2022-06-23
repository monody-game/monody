<?php

namespace App\Traits;

use Illuminate\Support\Str;

trait UUID
{
    public function initializeUUID(): void
    {
        $this->setKeyType('string');
        $this->setIncrementing(false);
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = Str::uuid()->toString();
            }
        });
    }
}
