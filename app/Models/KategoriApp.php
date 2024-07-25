<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ulid\Ulid;

class KategoriApp extends Model
{
    use HasFactory;
    protected $connection = 'mysql_1';
    protected $table = 'kategori_apps';
    protected $guarded = ['id'];
    public $incrementing = false;
    protected $keyType = 'string';

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = Ulid::generate();
            }
        });
    }
}
