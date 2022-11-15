<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GitHubResults extends Model
{

    protected $primaryKey = 'id';
    protected $table = 'appointments';
    protected $connection = 'mysql';
    protected $fillable = [];
    public $timestamps = false;

    protected function getRequestedAtFormattedAttribute ()
	{
		return date('d/m/Y H:i:s', strtotime($this->attributes['requested_at']));
	}

    protected function getResponsedAtFormattedAttribute ()
	{
		return date('d/m/Y H:i:s', strtotime($this->attributes['requested_at']));
	}

}