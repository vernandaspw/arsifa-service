<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ulid\Ulid;

class UserApp extends Model
{
    use HasFactory;
    protected $connection = 'mysql_1';
    protected $table = 'user_apps';

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

    public function filturs()
    {
        return $this->hasMany(UserFiltur::class);
    }
    public function roles()
    {
        return $this->hasMany(UserRole::class);
    }
}
