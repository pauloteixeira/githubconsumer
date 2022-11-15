<?php

namespace App\Services\Guzzle;

use \GuzzleHttp\Exception\GuzzleException;
use \GuzzleHttp\Client;
use App\Services\Guzzle\Promise;

class GuzzleService
{
    public $header;
    public $body;
    public $url;
    public $client;
    public $baseUri;

    public function client() 
    {
        if( $this->client instanceof Client ) {
            return $this->client;
        }

        $this->client = new Client(['timeout' => 200]);
    }

    public function get() 
    {
        $this->headerPreparing();

        return $this->client->get($this->url, ["headers" => $this->header]);
    }

    public function post() 
    {
        $this->headerPreparing();
        
        return $this->client->post($this->url,["headers" => $this->header, "body" => $this->body]);
    }

    private function headerPreparing() 
    {
        if( true == empty($this->url) ) {
            throw new \Exception("Atributo URL é obrigatório.");
        }

        if( true == empty($this->header) ) {
            $this->header = [];
        }

        if( true == empty($this->body) ) {
            $this->body = null;
        }

        if( false == $this->client instanceof Client ) {
            $this->client();
        }
    }
}