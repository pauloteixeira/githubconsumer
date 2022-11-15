<?php

namespace App\Services\Github;

use App\Services\Guzzle\GuzzleService as ApiService;
use App\Services\Guzzle\GuzzleAsyncService;

class GithubRequestService
{
    private $repository;
    private $owner;
    private const BASE_REPO_URL = "https://api.github.com/repos/";
    private const BASE_USER_URL = "https://api.github.com/users/";

    public function __construct( String $owner, String $repository )
    {
        $this->owner = $owner;
        $this->repository = $repository;
    }

    public function requestUser($user, $callback)
    {
        $api = new ApiService();
        $api->header = ["Accept" => "application/vnd.github+json", "Authorization" => "Bearer ". env("GITHUB_TOKEN_API"), "Content-Type" => "application/json"];
        $api->url = self::BASE_USER_URL . $user;

        $response = $api->get();
        $contents = ["result" => json_decode($response->getBody()->getContents(), true), "callback" => $callback];
        $result = [];

        if( count($contents) ) {
            $result = $contents;
        }

        return $contents;
    }

    public function requestIssues( $callback )
    {
        $api = new ApiService();
        $api->header = ["Accept" => "application/vnd.github+json", "Authorization" => "Bearer ". env("GITHUB_TOKEN_API"), "Content-Type" => "application/json"];
        $api->url = self::BASE_REPO_URL . $this->owner . "/" . $this->repository . "/issues";

        $response = $api->get();
        $contents = ["result" => json_decode($response->getBody()->getContents(), true), "callback" => $callback];
        $result = [];

        if( count($contents) ) {
            $result = $contents;
        }

        return $contents;
    }

    public function requestContributtors( $callback )
    {
        $api = new ApiService();
        $api->header = ["Accept" => "application/vnd.github+json", "Authorization" => "Bearer ". env("GITHUB_TOKEN_API"), "Content-Type" => "application/json"];
        $api->url = self::BASE_REPO_URL . $this->owner . "/" . $this->repository . "/contributors";
        
        $response = $api->get();
        $contents = ["result" => json_decode($response->getBody()->getContents(), true), "callback" => $callback];
        $result = [];

        if( count($contents) ) {
            $result = $contents;
        }

        return $contents;
    }

    
}