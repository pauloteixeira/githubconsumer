<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Appointments extends Model 
{

    protected $primaryKey = 'id';
    protected $table = 'appointments';
    protected $connection = 'mysql';
    protected $fillable = [];
    public $timestamps = false;

}