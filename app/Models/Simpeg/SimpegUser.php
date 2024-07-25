<?php

namespace App\Models\Simpeg;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SimpegUser extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $connection = 'mysql_2';
    protected $table = 'user';
}
