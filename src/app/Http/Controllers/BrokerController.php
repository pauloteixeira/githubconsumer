<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\RabbitMQ\PublisherService;

class BrokerController extends Controller
{
    public function index( Request $request ) {
        $publisher = new PublisherService();
        $publisher->connect();

        for( $i = 1; $i < 50; $i++ ){
            $publisher->publishMessage(json_encode(["id" => $i, "body" => "alo gente."]));
            echo $i . PHP_EOL;
        }
        
        dd("publicou!");
    }

    public function receive( Request $request ) {
        $publisher = new PublisherService();
        $publisher->connect();
        $publisher->receiveMessages();
    }

}