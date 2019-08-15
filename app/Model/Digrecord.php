<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Digrecord extends Model
{
    protected $table = 'digrecord';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $guarded = [];
}
