<?php

namespace App\Messages;

use App\Messages\IssuesMessage;
use App\Messages\ContributtorMessage;

class GithubMessage
{
    public $user;
    public $repository;
    public $issues;
    public $contributtors;
    public $published_at;
}