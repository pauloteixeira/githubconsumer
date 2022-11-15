<?php

namespace App\Services\Guzzle;

use \GuzzleHttp\Exception\GuzzleException;
use \GuzzleHttp\Client;
use \GuzzleHttp\Psr7\Request;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Exception\RequestException;
use App\Models\Appointments;

class GuzzleAsyncService
{
    public $header;
    public $body;
    public $url;
    public $client;
    public $baseUri;

    public $request;

    public function client() 
    {
        if( $this->client instanceof Client ) {
            return $this->client;
        }

        $this->client = new Client(['timeout' => 200]);
    }

    public function _get( $callback )
    {
        set_time_limit(200);
        try {
            $this->headerPreparing();
            $response = $this->client->getAsync($this->url, ["headers" => $this->header]);
            $promise->then(
                function ($response) use ($callback)
                {
                    //$response->getBody()->getContents()
                    $callback("testeee");
                }
            );
            //$promise->wait();
        }
        catch(\Exception $e)
        {
            throw new \Exception($e->getMessage());
        }

        return;
    }

    public function get( $callback ) 
    {
        set_time_limit(200);
        $this->headerPreparing();
        $this->request = new Request('GET', $this->url, ["headers" => $this->header]);

        try {
            $promise = $this->client
                ->sendAsync($this->request)
                ->then(function (ResponseInterface $response) use ($callback) {
                    $callback(json_decode($response->getBody()->getContents(), true));
                },
                function (RequestException $e) use ($callback)  {
                    $callback("Falhou");
                }); 
    
            $promise->wait();
        }
        catch(\Exception $e)
        {
            //
        }
    }

    public function post() 
    {
        $this->headerPreparing();
        
        return $this->client->post($this->url,["headers" => $this->header, $this->body]);
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